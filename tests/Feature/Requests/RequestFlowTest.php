<?php

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\User;

beforeEach(function () {
    $this->category = Category::create(['category_name' => 'أدوات', 'state' => 'active']);
    $this->condition = Condition::create(['condition_name' => 'جديد', 'order' => 1]);
    $this->owner = User::factory()->create(['full_name' => 'أحمد المالك']);
    $this->seeker = User::factory()->create(['full_name' => 'سارة الباحثة']);
});

function flowItem(array $attributes = []): Item
{
    return Item::create(array_merge([
        'owner_id' => test()->owner->id,
        'category_id' => test()->category->id,
        'condition_id' => test()->condition->id,
        'transaction_type' => 'sale',
        'title' => 'مثقب كهربائي',
        'price' => 5000,
        'count' => 1,
        'state' => 'active',
    ], $attributes));
}

function acceptRequest(ItemRequest $request): void
{
    test()->actingAs(test()->owner)->patch("/requests/{$request->id}/accept", [
        'owner_note' => 'أهلاً بك',
        'meeting_date' => now()->addDay()->format('Y-m-d H:i'),
        'meeting_location' => 'أمام الجامع',
    ])->assertSessionHasNoErrors();
}

test('a seeker sends a sale request and the owner is notified', function () {
    $item = flowItem();

    $this->actingAs($this->seeker)
        ->post("/items/{$item->id}/requests", ['requester_note' => 'متى يناسبك؟'])
        ->assertRedirect(route('requests.index'));

    $request = ItemRequest::first();
    expect($request->status)->toBe('waiting')->and($request->requester_note)->toBe('متى يناسبك؟');
    $this->assertDatabaseHas('notifications', ['user_id' => $this->owner->id, 'title' => 'طلب جديد']);
});

test('owners cannot request their own items and duplicates are blocked', function () {
    $item = flowItem();

    $this->actingAs($this->owner)->post("/items/{$item->id}/requests");
    expect(ItemRequest::count())->toBe(0);

    $this->actingAs($this->seeker)->post("/items/{$item->id}/requests");
    $this->actingAs($this->seeker)->post("/items/{$item->id}/requests");
    expect(ItemRequest::count())->toBe(1);
});

test('a rent request needs a period within the allowed duration', function () {
    $item = flowItem(['transaction_type' => 'rent', 'price' => 300, 'rent_unit' => 'day', 'max_rent_duration' => 3]);

    $this->actingAs($this->seeker)->post("/items/{$item->id}/requests")->assertSessionHasErrors(['start_date', 'end_date']);

    $this->actingAs($this->seeker)->post("/items/{$item->id}/requests", [
        'start_date' => today()->toDateString(),
        'end_date' => today()->addDays(5)->toDateString(),
    ])->assertSessionHasErrors('end_date');

    $this->actingAs($this->seeker)->post("/items/{$item->id}/requests", [
        'start_date' => today()->toDateString(),
        'end_date' => today()->addDays(2)->toDateString(),
    ])->assertSessionHasNoErrors();

    expect(ItemRequest::first()->rental->days())->toBe(3);
});

test('an exchange request needs an offer from my items or a description', function () {
    $item = flowItem(['transaction_type' => 'exchange', 'price' => 0]);
    $mine = flowItem(['owner_id' => $this->seeker->id, 'title' => 'جهاز لوحي']);

    $this->actingAs($this->seeker)->post("/items/{$item->id}/requests")->assertSessionHasErrors('offered_item_id');

    $this->actingAs($this->seeker)->post("/items/{$item->id}/requests", ['offered_item_id' => $mine->id])->assertSessionHasNoErrors();

    expect(ItemRequest::first()->exchangeOffer->label())->toBe('جهاز لوحي');
});

test('the seeker can cancel only before the owner accepts', function () {
    $item = flowItem();
    $this->actingAs($this->seeker)->post("/items/{$item->id}/requests");
    $request = ItemRequest::first();

    acceptRequest($request);

    $this->actingAs($this->seeker)->patch("/requests/{$request->id}/cancel");
    expect($request->fresh()->status)->toBe('approved');

    $other = flowItem(['title' => 'منشار']);
    $this->actingAs($this->seeker)->post("/items/{$other->id}/requests");
    $second = ItemRequest::latest('id')->first();

    $this->actingAs($this->seeker)->patch("/requests/{$second->id}/cancel");
    expect($second->fresh()->status)->toBe('canceled');
});

test('the owner rejects with a required note', function () {
    $item = flowItem();
    $this->actingAs($this->seeker)->post("/items/{$item->id}/requests");
    $request = ItemRequest::first();

    $this->actingAs($this->owner)->patch("/requests/{$request->id}/reject", ['owner_note' => ''])->assertSessionHasErrors('owner_note');
    $this->actingAs($this->owner)->patch("/requests/{$request->id}/reject", ['owner_note' => 'تم البيع لشخص آخر']);

    expect($request->fresh()->status)->toBe('rejected');
    $this->assertDatabaseHas('notifications', ['user_id' => $this->seeker->id, 'title' => 'تم رفض طلبك']);
});

test('a full sale: accept, confirm the appointment, receive', function () {
    $item = flowItem();
    $this->actingAs($this->seeker)->post("/items/{$item->id}/requests");
    $request = ItemRequest::first();

    // الباحث ما بيقدر يأكد الاستلام قبل القبول
    $this->actingAs($this->seeker)->patch("/requests/{$request->id}/receive");
    expect($request->fresh()->status)->toBe('waiting');

    acceptRequest($request);
    expect($request->fresh()->appointment->status)->toBe('new');

    // المالك اقترح الموعد، فالباحث هو اللي بيأكده
    $this->actingAs($this->owner)->patch("/requests/{$request->id}/appointment/confirm");
    expect($request->fresh()->appointment->status)->toBe('new');

    $this->actingAs($this->seeker)->patch("/requests/{$request->id}/appointment/confirm");
    expect($request->fresh()->appointment->status)->toBe('confirmed');

    $this->actingAs($this->seeker)->patch("/requests/{$request->id}/receive");

    expect($request->fresh()->status)->toBe('finished')
        ->and($item->fresh()->state)->toBe('sold');
});

test('proposing a new appointment cancels the old one and the other side confirms', function () {
    $item = flowItem();
    $this->actingAs($this->seeker)->post("/items/{$item->id}/requests");
    $request = ItemRequest::first();
    acceptRequest($request);

    $this->actingAs($this->seeker)->post("/requests/{$request->id}/appointment", [
        'meeting_date' => now()->addDays(2)->format('Y-m-d H:i'),
        'meeting_location' => 'الحديقة',
    ])->assertSessionHasNoErrors();

    expect($request->appointments()->where('status', 'canceled')->count())->toBe(1);

    $this->actingAs($this->owner)->patch("/requests/{$request->id}/appointment/confirm");
    expect($request->fresh()->appointment->meeting_location)->toBe('الحديقة')
        ->and($request->fresh()->appointment->status)->toBe('confirmed');
});

test('a full rental with return confirmed by the owner', function () {
    $item = flowItem(['transaction_type' => 'rent', 'price' => 300, 'rent_unit' => 'day', 'max_rent_duration' => 7]);
    $this->actingAs($this->seeker)->post("/items/{$item->id}/requests", [
        'start_date' => today()->toDateString(),
        'end_date' => today()->addDays(2)->toDateString(),
    ]);
    $request = ItemRequest::first();
    acceptRequest($request);

    $this->actingAs($this->seeker)->patch("/requests/{$request->id}/receive");
    expect($request->fresh()->status)->toBe('received')->and($item->fresh()->state)->toBe('rented');

    // المالك ما بيقدر يأكد قبل ما الباحث يبلغ عن الإرجاع
    $this->actingAs($this->owner)->patch("/requests/{$request->id}/return/confirm");
    expect($request->rental->fresh()->return_status)->toBe('pending');

    $this->actingAs($this->seeker)->patch("/requests/{$request->id}/return");
    $this->actingAs($this->owner)->patch("/requests/{$request->id}/return/confirm");

    expect($request->rental->fresh()->return_status)->toBe('confirmed')
        ->and($request->fresh()->status)->toBe('finished')
        ->and($item->fresh()->state)->toBe('active');
});

test('a return is auto-confirmed after 48 hours without a reply', function () {
    $item = flowItem(['transaction_type' => 'rent', 'price' => 300, 'rent_unit' => 'day', 'max_rent_duration' => 7, 'state' => 'rented']);
    $request = ItemRequest::create(['item_id' => $item->id, 'requester_id' => $this->seeker->id, 'status' => 'received']);
    $rental = $request->rental()->create([
        'start_date' => today()->subDays(5),
        'expected_return_date' => today()->subDays(3),
        'return_status' => 'pending',
        'return_reported_at' => now()->subHours(49),
    ]);

    $this->artisan('rentals:auto-confirm')->assertSuccessful();

    expect($rental->fresh()->return_status)->toBe('auto_confirmed')
        ->and($request->fresh()->status)->toBe('finished')
        ->and($item->fresh()->state)->toBe('active');
});

test('a dispute turns into a report for the admin', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $item = flowItem(['transaction_type' => 'rent', 'price' => 300, 'rent_unit' => 'day', 'max_rent_duration' => 7, 'state' => 'rented']);
    $request = ItemRequest::create(['item_id' => $item->id, 'requester_id' => $this->seeker->id, 'status' => 'received']);
    $request->rental()->create([
        'start_date' => today()->subDays(3),
        'expected_return_date' => today()->subDay(),
        'return_status' => 'pending',
        'return_reported_at' => now()->subHour(),
    ]);

    $this->actingAs($this->owner)->patch("/requests/{$request->id}/return/dispute", ['reason' => 'قصير'])->assertSessionHasErrors('reason');
    $this->actingAs($this->owner)->patch("/requests/{$request->id}/return/dispute", ['reason' => 'أُرجع المثقب بدون الشاحن']);

    expect($request->rental->fresh()->return_status)->toBe('disputed');
    $this->assertDatabaseHas('reports', ['request_id' => $request->id, 'reporter_id' => $this->owner->id, 'status' => 'pending']);
    $this->assertDatabaseHas('notifications', ['user_id' => $admin->id, 'title' => 'بلاغ جديد']);
});

test('contact details are visible only after acceptance', function () {
    $item = flowItem();
    $request = ItemRequest::create(['item_id' => $item->id, 'requester_id' => $this->seeker->id, 'status' => 'waiting']);

    expect($request->contactVisible())->toBeFalse();

    $request->status = 'approved';
    expect($request->contactVisible())->toBeTrue();
});

test('strangers cannot act on a request', function () {
    $item = flowItem();
    $request = ItemRequest::create(['item_id' => $item->id, 'requester_id' => $this->seeker->id, 'status' => 'waiting']);
    $stranger = User::factory()->create();

    $this->actingAs($stranger)->patch("/requests/{$request->id}/accept")->assertForbidden();
    $this->actingAs($stranger)->patch("/requests/{$request->id}/cancel")->assertForbidden();
    $this->actingAs($this->seeker)->patch("/requests/{$request->id}/accept")->assertForbidden();
});

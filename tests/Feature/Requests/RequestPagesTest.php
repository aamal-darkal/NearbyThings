<?php

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\User;

beforeEach(function () {
    $category = Category::create(['category_name' => 'أدوات', 'state' => 'active']);
    $condition = Condition::create(['condition_name' => 'جديد', 'order' => 1]);
    $this->owner = User::factory()->create(['full_name' => 'أحمد المالك', 'phone' => '0933111222']);
    $this->seeker = User::factory()->create(['full_name' => 'سارة الباحثة', 'phone' => '0944222333']);

    $this->item = Item::create([
        'owner_id' => $this->owner->id, 'category_id' => $category->id, 'condition_id' => $condition->id,
        'transaction_type' => 'rent', 'title' => 'مثقب Bosch', 'price' => 300, 'rent_unit' => 'day',
        'max_rent_duration' => 7, 'state' => 'active',
    ]);

    $this->deal = ItemRequest::create([
        'item_id' => $this->item->id, 'requester_id' => $this->seeker->id, 'status' => 'waiting',
        'requester_note' => 'لتركيب رفوف',
    ]);
    $this->deal->rental()->create([
        'start_date' => today(), 'expected_return_date' => today()->addDays(2), 'return_status' => 'pending',
    ]);
});

test('the item page shows the request form for a rental', function () {
    $this->actingAs($this->seeker->fresh());
    $this->deal->update(['status' => 'canceled']);

    $this->get("/items/{$this->item->id}")
        ->assertOk()
        ->assertSee('إرسال طلب إلى المالك')
        ->assertSee('start_date', false);
});

test('my requests and incoming requests pages list the request', function () {
    $this->actingAs($this->seeker)->get('/requests')
        ->assertOk()->assertSee('مثقب Bosch')->assertSee('إلغاء الطلب')->assertSee('900 ل.س');

    $this->actingAs($this->owner)->get('/requests/incoming')
        ->assertOk()->assertSee('سارة الباحثة')->assertSee('لتركيب رفوف')->assertSee('قبول الطلب');
});

test('contact details appear on the deal page only after acceptance', function () {
    $this->actingAs($this->seeker)->get("/requests/{$this->deal->id}")
        ->assertOk()
        ->assertSee('بانتظار رد المالك')
        ->assertDontSee('0933111222');

    $this->deal->update(['status' => 'approved']);
    $this->deal->appointments()->create([
        'proposed_by' => $this->owner->id, 'meeting_date' => now()->addDay(), 'meeting_location' => 'أمام الجامع', 'status' => 'new',
    ]);

    $this->actingAs($this->seeker)->get("/requests/{$this->deal->id}")
        ->assertOk()
        ->assertSee('0933111222')
        ->assertSee('تأكيد الموعد')
        ->assertSee('أمام الجامع');

    $this->actingAs($this->owner)->get("/requests/{$this->deal->id}")
        ->assertOk()
        ->assertSee('0944222333')
        ->assertSee('بانتظار تأكيد الجار للموعد');
});

test('the owner sees confirm and dispute after the return is reported', function () {
    $this->deal->update(['status' => 'received']);
    $this->deal->rental->update(['return_reported_at' => now()]);

    $this->actingAs($this->owner)->get("/requests/{$this->deal->id}")
        ->assertOk()
        ->assertSee('تأكيد الإرجاع')
        ->assertSee('الاعتراض على الإرجاع');
});

test('strangers cannot open a deal page', function () {
    $this->actingAs(User::factory()->create())->get("/requests/{$this->deal->id}")->assertForbidden();
});

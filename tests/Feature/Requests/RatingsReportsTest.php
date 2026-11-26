<?php

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\Notification;
use App\Models\Report;
use App\Models\User;

beforeEach(function () {
    $category = Category::create(['category_name' => 'أدوات', 'state' => 'active']);
    $condition = Condition::create(['condition_name' => 'جديد', 'order' => 1]);
    $this->owner = User::factory()->create(['full_name' => 'أحمد المالك']);
    $this->seeker = User::factory()->create(['full_name' => 'سارة الباحثة']);
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();

    $this->item = Item::create([
        'owner_id' => $this->owner->id, 'category_id' => $category->id, 'condition_id' => $condition->id,
        'transaction_type' => 'sale', 'title' => 'مثقب Bosch', 'price' => 5000, 'state' => 'active',
    ]);
    $this->deal = ItemRequest::create(['item_id' => $this->item->id, 'requester_id' => $this->seeker->id, 'status' => 'finished']);
});

test('both sides rate each other once after the deal and the final rate is the average', function () {
    $this->actingAs($this->seeker)->post("/requests/{$this->deal->id}/review", ['rating' => 5, 'comment' => 'ممتاز'])->assertSessionHasNoErrors();
    $this->actingAs($this->seeker)->post("/requests/{$this->deal->id}/review", ['rating' => 1]);

    expect($this->owner->fresh()->rate)->toBe(5.0);

    $other = Item::create([
        'owner_id' => $this->owner->id, 'category_id' => $this->item->category_id, 'condition_id' => $this->item->condition_id,
        'transaction_type' => 'sale', 'title' => 'منشار', 'price' => 100, 'state' => 'active',
    ]);
    $second = ItemRequest::create(['item_id' => $other->id, 'requester_id' => $this->seeker->id, 'status' => 'finished']);
    $this->actingAs($this->seeker)->post("/requests/{$second->id}/review", ['rating' => 2]);

    expect($this->owner->fresh()->rate)->toBe(3.5);

    $this->actingAs($this->owner)->post("/requests/{$this->deal->id}/review", ['rating' => 4]);
    expect($this->seeker->fresh()->rate)->toBe(4.0);
});

test('rating is only allowed for finished deals', function () {
    $this->deal->update(['status' => 'approved']);

    $this->actingAs($this->seeker)->post("/requests/{$this->deal->id}/review", ['rating' => 5]);

    expect($this->owner->fresh()->rate)->toBe(0.0);
});

test('a user reports an item and admins are notified', function () {
    $this->actingAs($this->seeker)->post('/reports?item='.$this->item->id, ['category' => 'fraud', 'reason' => 'الصور لا تطابق الغرض الحقيقي'])
        ->assertRedirect(route('items.show', $this->item));

    $report = Report::first();
    expect($report->category)->toBe('fraud')->and($report->item_id)->toBe($this->item->id);
    $this->assertDatabaseHas('notifications', ['user_id' => $this->admin->id, 'title' => 'بلاغ جديد']);

    // بلاغ مكرر ما بينقبل
    $this->actingAs($this->seeker)->post('/reports?item='.$this->item->id, ['category' => 'fraud', 'reason' => 'الصور لا تطابق الغرض الحقيقي']);
    expect(Report::count())->toBe(1);
});

test('owners cannot report their own items and strangers cannot report a deal', function () {
    $this->actingAs($this->owner)->post('/reports?item='.$this->item->id, ['category' => 'other', 'reason' => 'تجربة بلاغ على غرضي'])->assertForbidden();
    $this->actingAs(User::factory()->create())->post('/reports?request='.$this->deal->id, ['category' => 'other', 'reason' => 'تجربة بلاغ على عملية'])->assertForbidden();
});

test('the admin resolves a report by banning the reported user', function () {
    $this->actingAs($this->seeker)->post('/reports?request='.$this->deal->id, ['category' => 'fraud', 'reason' => 'المثقب لا يعمل أبداً']);
    $report = Report::first();

    expect($report->reportedUser()->id)->toBe($this->owner->id);

    $this->actingAs($this->admin)->patch("/admin/reports/{$report->id}/resolve", ['action' => 'ban_user', 'admin_note' => 'مخالفة متكررة']);

    expect($report->fresh()->status)->toBe('resolved')
        ->and($this->owner->fresh()->is_blocked)->toBeTrue();
    $this->assertDatabaseHas('notifications', ['user_id' => $this->seeker->id, 'title' => 'تمت مراجعة بلاغك']);
});

test('the admin blocks the item or rejects the report', function () {
    $this->actingAs($this->seeker)->post('/reports?item='.$this->item->id, ['category' => 'prohibited', 'reason' => 'غرض غير مسموح بتداوله']);
    $report = Report::first();

    $this->actingAs($this->admin)->patch("/admin/reports/{$report->id}/resolve", ['action' => 'block_item']);
    expect($this->item->fresh()->state)->toBe('banned');

    $second = Report::create(['reporter_id' => $this->seeker->id, 'item_id' => $this->item->id, 'category' => 'other', 'reason' => 'سبب آخر للبلاغ', 'status' => 'pending']);
    $this->actingAs($this->admin)->patch("/admin/reports/{$second->id}/resolve", ['action' => 'reject']);
    expect($second->fresh()->status)->toBe('rejected');
});

test('opening a notification marks it read and follows its link', function () {
    $notification = Notification::send($this->seeker->id, 'طلب', 'نص', route('requests.show', $this->deal));

    $this->actingAs($this->seeker)->get("/notifications/{$notification->id}")->assertRedirect(route('requests.show', $this->deal));
    expect($notification->fresh()->is_read)->toBeTrue();

    $this->actingAs($this->owner)->get("/notifications/{$notification->id}")->assertForbidden();
});

test('external notification links are ignored', function () {
    $notification = Notification::send($this->seeker->id, 'طلب', 'نص', 'https://evil.example.com');

    $this->actingAs($this->seeker)->get("/notifications/{$notification->id}")->assertRedirect(route('notifications.index'));
});

test('mark all notifications as read', function () {
    Notification::send($this->seeker->id, 'أ', 'نص');
    Notification::send($this->seeker->id, 'ب', 'نص');

    $this->actingAs($this->seeker)->patch('/notifications/read-all');

    expect($this->seeker->unreadNotificationsCount())->toBe(0);
});

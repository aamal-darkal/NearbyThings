<?php

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\Notification;
use App\Models\Report;
use App\Models\Review;
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
        'transaction_type' => 'sale', 'title' => 'مثقب Bosch', 'price' => 5000, 'state' => 'sold',
    ]);
    $this->deal = ItemRequest::create(['item_id' => $this->item->id, 'requester_id' => $this->seeker->id, 'status' => 'finished']);
});

test('a finished deal shows the rating form, then the given rating', function () {
    $this->actingAs($this->seeker)->get("/requests/{$this->deal->id}")
        ->assertOk()->assertSee('إرسال التقييم')->assertSee('الإبلاغ عن مشكلة في هذه العملية');

    Review::create(['request_id' => $this->deal->id, 'reviewer_id' => $this->seeker->id, 'reviewed_user_id' => $this->owner->id, 'rating' => 4, 'comment' => 'تعامل راقٍ']);

    $this->actingAs($this->seeker)->get("/requests/{$this->deal->id}")
        ->assertOk()->assertDontSee('إرسال التقييم')->assertSee('تعامل راقٍ');

    $this->actingAs($this->owner)->get('/profile')->assertOk()->assertSee('تعامل راقٍ');
});

test('the report page renders for an item and a deal', function () {
    $this->actingAs($this->seeker)->get('/reports/create?item='.$this->item->id)->assertOk()->assertSee('الإبلاغ عن مخالفة')->assertSee('مثقب Bosch');
    $this->actingAs($this->seeker)->get('/reports/create?request='.$this->deal->id)->assertOk()->assertSee('البلاغ مرتبط بعملية');
});

test('the notifications page groups notifications and shows unread ones', function () {
    Notification::send($this->seeker->id, 'تمت الموافقة على طلبك', 'وافق المالك على طلبك', route('requests.show', $this->deal));

    $this->actingAs($this->seeker)->get('/notifications')
        ->assertOk()->assertSee('اليوم')->assertSee('وافق المالك على طلبك')->assertSee('تعليم الكل كمقروء');

    $this->actingAs($this->seeker)->get('/notifications?filter=unread')->assertOk()->assertSee('وافق المالك على طلبك');
});

test('the history page lists finished deals with a rate-now button', function () {
    $this->actingAs($this->seeker)->get('/history')
        ->assertOk()->assertSee('مثقب Bosch')->assertSee('قيّم الآن')->assertSee('أحمد المالك');

    $this->actingAs($this->seeker)->get('/history?role=owner')->assertOk()->assertDontSee('مثقب Bosch');
    $this->actingAs($this->owner)->get('/history?role=owner')->assertOk()->assertSee('مثقب Bosch');
});

test('the admin reports page and drawer render', function () {
    $report = Report::create([
        'reporter_id' => $this->seeker->id, 'request_id' => $this->deal->id, 'category' => 'fraud',
        'reason' => 'المثقب لا يعمل', 'status' => 'pending',
    ]);

    $this->actingAs($this->admin)->get('/admin/reports')->assertOk()->assertSee('خداع في التعامل')->assertDontSee('تفاصيل البلاغ');
    $this->actingAs($this->admin)->get('/admin/reports?report='.$report->id)
        ->assertOk()->assertSee('تفاصيل البلاغ')->assertSee('المثقب لا يعمل')->assertSee('حظر المستخدم (أحمد المالك)');
});

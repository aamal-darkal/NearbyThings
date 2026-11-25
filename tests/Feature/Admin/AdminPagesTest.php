<?php

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->admin = User::factory()->create(['full_name' => 'مدير النظام']);
    $this->admin->forceFill(['is_admin' => true])->save();

    $category = Category::create(['category_name' => 'أثاث', 'icon' => '🛋️', 'state' => 'active']);
    Category::create(['category_name' => 'معدات تصوير', 'state' => 'new']);
    $condition = Condition::create(['condition_name' => 'جديد', 'order' => 1]);

    $this->owner = User::factory()->create(['full_name' => 'أحمد الخطيب']);
    $this->requester = User::factory()->create(['full_name' => 'سارة العلي']);

    $item = Item::create([
        'owner_id' => $this->owner->id, 'category_id' => $category->id, 'condition_id' => $condition->id,
        'transaction_type' => 'rent', 'title' => 'خيمة رحلات', 'price' => 500, 'rent_unit' => 'day', 'state' => 'active',
    ]);

    $requestId = DB::table('requests')->insertGetId([
        'item_id' => $item->id, 'requester_id' => $this->requester->id, 'status' => 'waiting',
    ]);
    DB::table('reports')->insert([
        'reporter_id' => $this->requester->id, 'request_id' => $requestId, 'reason' => 'الخيمة ممزقة', 'status' => 'pending',
    ]);
});

test('all admin pages render', function () {
    $this->actingAs($this->admin)->get('/admin')->assertOk()->assertSee('لوحة التحكم')->assertSee('الخيمة ممزقة');
    $this->actingAs($this->admin)->get('/admin/listings')->assertOk()->assertSee('خيمة رحلات');
    $this->actingAs($this->admin)->get('/admin/listings?tab=requests')->assertOk()->assertSee('سارة العلي');
    $this->actingAs($this->admin)->get('/admin/categories')->assertOk()->assertSee('أثاث');
    $this->actingAs($this->admin)->get('/admin/categories?tab=conditions')->assertOk()->assertSee('جديد');
    $this->actingAs($this->admin)->get('/admin/suggestions')->assertOk()->assertSee('معدات تصوير');
    $this->actingAs($this->admin)->get('/admin/users')->assertOk()->assertSee('أحمد الخطيب');
});

test('the user drawer shows reports against the user', function () {
    $this->actingAs($this->admin)
        ->get('/admin/users?user='.$this->owner->id)
        ->assertOk()
        ->assertSee('ملف المستخدم')
        ->assertSee('الخيمة ممزقة')
        ->assertSee('حظر المستخدم');
});

test('admin filters work', function () {
    $this->actingAs($this->admin)->get('/admin/listings?type=sale')->assertOk()->assertDontSee('خيمة رحلات');
    $this->actingAs($this->admin)->get('/admin/users?q=سارة')->assertOk()->assertDontSee('أحمد الخطيب');
});

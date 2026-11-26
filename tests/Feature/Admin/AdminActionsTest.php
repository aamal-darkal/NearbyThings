<?php

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();

    $this->category = Category::create(['category_name' => 'أثاث', 'icon' => '🛋️', 'state' => 'active']);
    $this->condition = Condition::create(['condition_name' => 'جديد', 'order' => 1]);
    $this->owner = User::factory()->create();

    $this->item = Item::create([
        'owner_id' => $this->owner->id,
        'category_id' => $this->category->id,
        'condition_id' => $this->condition->id,
        'transaction_type' => 'sale',
        'title' => 'هاتف مزيف',
        'price' => 100,
        'state' => 'active',
    ]);
});

test('non admins cannot open admin pages', function () {
    $this->actingAs($this->owner)->get('/admin')->assertForbidden();
    $this->actingAs($this->owner)->patch("/admin/items/{$this->item->id}/block")->assertForbidden();
});

test('an admin can block and unblock an item', function () {
    $this->actingAs($this->admin)->patch("/admin/items/{$this->item->id}/block");

    expect($this->item->fresh()->state)->toBe('banned');
    $this->assertDatabaseHas('notifications', ['user_id' => $this->owner->id, 'title' => 'تم حجب إعلانك']);

    $this->actingAs($this->admin)->patch("/admin/items/{$this->item->id}/unblock");
    expect($this->item->fresh()->state)->toBe('inactive');
});

test('an admin can ban and unban a user with a reason', function () {
    $this->actingAs($this->admin)
        ->patch("/admin/users/{$this->owner->id}/ban", ['reason' => ''])
        ->assertSessionHasErrors('reason');

    $this->actingAs($this->admin)
        ->patch("/admin/users/{$this->owner->id}/ban", ['reason' => 'نشر إعلانات احتيالية']);

    expect($this->owner->fresh()->is_blocked)->toBeTrue()
        ->and(Item::visible()->count())->toBe(0);

    $this->actingAs($this->admin)->patch("/admin/users/{$this->owner->id}/unban");
    expect($this->owner->fresh()->is_blocked)->toBeFalse();
});

test('admins cannot be banned', function () {
    $other = User::factory()->create();
    $other->forceFill(['is_admin' => true])->save();

    $this->actingAs($this->admin)->patch("/admin/users/{$other->id}/ban", ['reason' => 'سبب الحظر']);

    expect($other->fresh()->is_blocked)->toBeFalse();
});

test('an admin manages categories', function () {
    $this->actingAs($this->admin)
        ->post('/admin/categories', ['category_name' => 'معدات تصوير', 'icon' => '📷'])
        ->assertSessionHasNoErrors();

    $category = Category::where('category_name', 'معدات تصوير')->first();
    expect($category->state)->toBe('active');

    $this->actingAs($this->admin)->patch("/admin/categories/{$category->id}/toggle");
    expect($category->fresh()->state)->toBe('banned');

    // تصنيف عليه إعلانات ما بينحذف
    $this->actingAs($this->admin)->delete("/admin/categories/{$this->category->id}");
    expect(Category::find($this->category->id))->not->toBeNull();

    $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");
    expect(Category::find($category->id))->toBeNull();
});

test('an admin manages item conditions', function () {
    $this->actingAs($this->admin)
        ->post('/admin/conditions', ['condition_name' => 'مستعمل', 'order' => 2])
        ->assertSessionHasNoErrors();

    $condition = Condition::where('condition_name', 'مستعمل')->first();

    $this->actingAs($this->admin)->put("/admin/conditions/{$condition->id}", ['condition_name' => 'مستعمل بحالة جيدة', 'order' => 3]);
    expect($condition->fresh()->order)->toBe(3);

    $this->actingAs($this->admin)->delete("/admin/conditions/{$this->condition->id}");
    expect(Condition::find($this->condition->id))->not->toBeNull();
});

test('an admin approves a suggested category', function () {
    $suggested = Category::create(['category_name' => 'معدات تصوير', 'state' => 'new']);
    $this->item->update(['category_id' => $suggested->id]);

    $this->actingAs($this->admin)->patch("/admin/suggestions/{$suggested->id}/approve");

    expect($suggested->fresh()->state)->toBe('active');
    $this->assertDatabaseHas('notifications', ['user_id' => $this->owner->id, 'title' => 'تمت الموافقة على التصنيف']);
});

test('an admin moves a suggestion to an existing category', function () {
    $suggested = Category::create(['category_name' => 'كراسي', 'state' => 'new']);
    $this->item->update(['category_id' => $suggested->id]);

    $this->actingAs($this->admin)->patch("/admin/suggestions/{$suggested->id}/move", ['target_id' => $this->category->id]);

    expect(Category::find($suggested->id))->toBeNull()
        ->and($this->item->fresh()->category_id)->toBe($this->category->id);
});

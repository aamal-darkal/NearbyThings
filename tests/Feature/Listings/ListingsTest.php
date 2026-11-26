<?php

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');

    $this->category = Category::create(['category_name' => 'أثاث', 'state' => 'active']);
    $this->condition = Condition::create(['condition_name' => 'جديد', 'order' => 1]);
    $this->owner = User::factory()->create(['latitude' => 33.5, 'longitude' => 36.3, 'address' => 'المزة']);
});

// بيانات نموذج صالحة
function itemPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'طاولة خشبية',
        'category_id' => test()->category->id,
        'condition_id' => test()->condition->id,
        'count' => 1,
        'description' => 'طاولة خشبية بحالة ممتازة جداً',
        'transaction_type' => 'sale',
        'price' => 5000,
        'location_mode' => 'account',
        'location_hint' => 'المزة',
        'images' => [UploadedFile::fake()->image('table.jpg')],
    ], $overrides);
}

function makeItem(User $owner, array $attributes = []): Item
{
    return Item::create(array_merge([
        'owner_id' => $owner->id,
        'category_id' => test()->category->id,
        'condition_id' => test()->condition->id,
        'transaction_type' => 'sale',
        'title' => 'غرض تجريبي',
        'price' => 1000,
        'state' => 'active',
    ], $attributes));
}

function addRequest(Item $item, string $status): void
{
    DB::table('requests')->insert([
        'item_id' => $item->id,
        'requester_id' => User::factory()->create()->id,
        'status' => $status,
    ]);
}

test('an owner can publish a listing with images', function () {
    $this->actingAs($this->owner)->post('/items', itemPayload())
        ->assertRedirect(route('items.mine'));

    $item = Item::first();
    expect($item->title)->toBe('طاولة خشبية')
        ->and($item->state)->toBe('active')
        ->and($item->latitude)->toBe(33.5)
        ->and($item->images)->toHaveCount(1);

    Storage::disk('uploads')->assertExists($item->images->first()->image_path);
    $this->assertDatabaseHas('activity_logs', ['table_name' => 'items', 'action' => 'create']);
});

test('an exchange listing needs no price and a rent listing needs a duration', function () {
    $this->actingAs($this->owner)
        ->post('/items', itemPayload(['transaction_type' => 'exchange', 'price' => null, 'exchange_for' => 'دراجة']))
        ->assertSessionHasNoErrors();

    expect(Item::first()->price)->toBe(0.0)
        ->and(Item::first()->variants['exchange_for'])->toBe('دراجة');

    $this->actingAs($this->owner)
        ->post('/items', itemPayload(['transaction_type' => 'rent', 'price' => 300]))
        ->assertSessionHasErrors(['max_rent_duration', 'rent_unit']);
});

test('a suggested category is created as new and admins are notified', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($this->owner)
        ->post('/items', itemPayload(['category_id' => null, 'suggested_category' => 'معدات تصوير']))
        ->assertSessionHasNoErrors();

    $category = Category::where('category_name', 'معدات تصوير')->first();
    expect($category->state)->toBe('new')
        ->and(Item::first()->category_id)->toBe($category->id);

    $this->assertDatabaseHas('notifications', ['user_id' => $admin->id, 'title' => 'اقتراح تصنيف جديد']);
});

test('the account location is required when the account has none', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/items', itemPayload())->assertSessionHasErrors('latitude');

    $this->actingAs($user)
        ->post('/items', itemPayload(['location_mode' => 'custom', 'latitude' => 33.51234, 'longitude' => 36.27891]))
        ->assertSessionHasNoErrors();

    expect(Item::first()->latitude)->toBe(33.512);
});

test('only the owner can edit or delete a listing', function () {
    $item = makeItem($this->owner);
    $other = User::factory()->create();

    $this->actingAs($other)->delete("/items/{$item->id}")->assertForbidden();
    $this->actingAs($other)->patch("/items/{$item->id}/toggle")->assertForbidden();
});

test('a listing with open requests cannot be edited or paused', function () {
    $item = makeItem($this->owner);
    addRequest($item, 'waiting');

    $this->actingAs($this->owner)
        ->put("/items/{$item->id}", itemPayload(['title' => 'عنوان جديد']))
        ->assertRedirect(route('items.mine'));
    expect($item->fresh()->title)->toBe('غرض تجريبي');

    $this->actingAs($this->owner)->patch("/items/{$item->id}/toggle");
    expect($item->fresh()->state)->toBe('active');
});

test('a listing can be paused and activated again', function () {
    $item = makeItem($this->owner);

    $this->actingAs($this->owner)->patch("/items/{$item->id}/toggle");
    expect($item->fresh()->state)->toBe('inactive');

    $this->actingAs($this->owner)->patch("/items/{$item->id}/toggle");
    expect($item->fresh()->state)->toBe('active');
});

test('a listing is deleted only when it has no requests', function () {
    $withRequest = makeItem($this->owner);
    addRequest($withRequest, 'finished');
    $free = makeItem($this->owner);

    $this->actingAs($this->owner)->delete("/items/{$withRequest->id}");
    $this->actingAs($this->owner)->delete("/items/{$free->id}");

    expect(Item::find($withRequest->id))->not->toBeNull()
        ->and(Item::find($free->id))->toBeNull();
});

test('editing keeps old images and removes the selected ones', function () {
    $item = makeItem($this->owner);
    $keep = $item->images()->create(['image_path' => 'items/keep.jpg']);
    $remove = $item->images()->create(['image_path' => 'items/remove.jpg']);

    $this->actingAs($this->owner)->put("/items/{$item->id}", itemPayload([
        'images' => [],
        'remove_images' => [$remove->id],
    ]))->assertSessionHasNoErrors();

    expect($item->images()->pluck('id')->all())->toBe([$keep->id]);
});

test('the listing pages render', function () {
    $item = makeItem($this->owner, ['location_hint' => 'المزة']);
    $item->images()->create(['image_path' => 'items/a.jpg']);
    addRequest(makeItem($this->owner, ['title' => 'عليه طلب']), 'waiting');

    $this->actingAs($this->owner)->get('/my-items')->assertOk()->assertSee('غرض تجريبي')->assertSee('قيد التفاوض والاتفاق');
    $this->actingAs($this->owner)->get('/my-items?tab=deal')->assertOk()->assertSee('عليه طلب')->assertDontSee('غرض تجريبي');
    $this->actingAs($this->owner)->get('/items/create')->assertOk()->assertSee('إضافة غرض جديد');
    $this->actingAs($this->owner)->get("/items/{$item->id}/edit")->assertOk()->assertSee('غرض تجريبي');
});

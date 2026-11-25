<?php

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\User;

beforeEach(function () {
    $this->bikes = Category::create(['category_name' => 'دراجات', 'state' => 'active']);
    $this->suggested = Category::create(['category_name' => 'معدات تصوير', 'state' => 'new']);
    $this->condition = Condition::create(['condition_name' => 'جديد', 'order' => 1]);
    $this->owner = User::factory()->create();

    // المستخدم بالمزة
    $this->user = User::factory()->create(['latitude' => 33.5032, 'longitude' => 36.2541]);
});

function pageItem(array $attributes): Item
{
    return Item::create(array_merge([
        'owner_id' => test()->owner->id,
        'category_id' => test()->bikes->id,
        'condition_id' => test()->condition->id,
        'transaction_type' => 'sale',
        'price' => 1000,
        'state' => 'active',
        'latitude' => 33.505,
        'longitude' => 36.255,
    ], $attributes));
}

test('search filters by keyword, type, price and distance', function () {
    pageItem(['title' => 'دراجة جبلية قريبة', 'price' => 12000]);
    pageItem(['title' => 'دراجة للإيجار', 'transaction_type' => 'rent', 'price' => 300, 'rent_unit' => 'day']);
    pageItem(['title' => 'دراجة بعيدة جداً', 'latitude' => 33.60, 'longitude' => 36.35]);
    pageItem(['title' => 'كنبة قريبة']);

    $this->actingAs($this->user);

    $this->get('/search?q=دراجة')
        ->assertOk()
        ->assertSee('دراجة جبلية قريبة')
        ->assertSee('دراجة للإيجار')
        ->assertDontSee('دراجة بعيدة جداً')
        ->assertDontSee('كنبة قريبة');

    $this->get('/search?q=دراجة&types[]=rent')->assertSee('دراجة للإيجار')->assertDontSee('دراجة جبلية قريبة');
    $this->get('/search?q=دراجة&max_price=5000')->assertSee('دراجة للإيجار')->assertDontSee('دراجة جبلية قريبة');
    $this->get('/search?q=دراجة&radius=20')->assertSee('دراجة بعيدة جداً')->assertDontSee('كنبة قريبة');
});

test('hidden items never appear in search', function () {
    pageItem(['title' => 'غرض موقوف', 'state' => 'inactive']);
    pageItem(['title' => 'غرض محجوب', 'state' => 'banned']);

    $blocked = User::factory()->create();
    $blocked->forceFill(['is_blocked' => true])->save();
    pageItem(['title' => 'غرض محظور', 'owner_id' => $blocked->id]);

    $this->actingAs($this->user)->get('/search')
        ->assertOk()
        ->assertDontSee('غرض موقوف')
        ->assertDontSee('غرض محجوب')
        ->assertDontSee('غرض محظور');
});

test('a suggested category is searchable but not usable as a filter', function () {
    pageItem(['title' => 'كاميرا كانون', 'category_id' => $this->suggested->id]);

    $this->actingAs($this->user)->get('/search?q=كاميرا')->assertSee('كاميرا كانون');
    $this->actingAs($this->user)->get('/search?category='.$this->suggested->id)->assertDontSee('كاميرا كانون');
});

test('the item page shows an approximate location and hides banned items', function () {
    $item = pageItem(['title' => 'دراجة جبلية', 'location_hint' => 'المزة']);
    $banned = pageItem(['title' => 'محجوب', 'state' => 'banned']);

    $this->get("/items/{$item->id}")->assertOk()->assertSee('دراجة جبلية')->assertSee('المزة');
    $this->get("/items/{$banned->id}")->assertNotFound();
    $this->actingAs($this->owner)->get("/items/{$banned->id}")->assertOk();
});

test('the item page never shows the owner phone or email', function () {
    $this->owner->update(['phone' => '0933999888']);
    $item = pageItem(['title' => 'دراجة جبلية']);

    $this->actingAs($this->user)->get("/items/{$item->id}")
        ->assertDontSee('0933999888')
        ->assertDontSee($this->owner->email);
});

test('the home page renders', function () {
    pageItem(['title' => 'دراجة قريبة من البيت']);

    $this->actingAs($this->user)->get('/')->assertOk();
});

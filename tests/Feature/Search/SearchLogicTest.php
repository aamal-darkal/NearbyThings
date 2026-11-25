<?php

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\User;
use App\Support\Geo;
use App\Support\ItemSearch;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->category = Category::create(['category_name' => 'دراجات', 'state' => 'active']);
    $this->condition = Condition::create(['condition_name' => 'جديد', 'order' => 1]);
    $this->owner = User::factory()->create();
});

function logicItem(array $attributes): Item
{
    return Item::create(array_merge([
        'owner_id' => test()->owner->id,
        'category_id' => test()->category->id,
        'condition_id' => test()->condition->id,
        'transaction_type' => 'sale',
        'title' => 'دراجة',
        'price' => 1000,
        'state' => 'active',
    ], $attributes));
}

test('distance labels use meters and kilometers', function () {
    expect(Geo::label(0.8))->toBe('800 متر')
        ->and(Geo::label(1.24))->toBe('1.2 كم')
        ->and(Geo::label(3.0))->toBe('3 كم');
});

test('items are sorted by distance and limited by the radius', function () {
    $far = logicItem(['title' => 'بعيد', 'latitude' => 33.60, 'longitude' => 36.40]);
    $near = logicItem(['title' => 'قريب', 'latitude' => 33.504, 'longitude' => 36.255]);
    $middle = logicItem(['title' => 'وسط', 'latitude' => 33.52, 'longitude' => 36.27]);
    logicItem(['title' => 'بدون موقع']);

    $results = ItemSearch::withDistance(Item::query(), 33.5032, 36.2541, 5);

    expect($results->pluck('id')->all())->toBe([$near->id, $middle->id])
        ->and($results->first()->distance)->toBeLessThan(1);

    expect(ItemSearch::withDistance(Item::query(), 33.5032, 36.2541)->pluck('id')->all())
        ->toHaveCount(4)
        ->and(ItemSearch::withDistance(Item::query(), 33.5032, 36.2541)->last()->title)->toBe('بدون موقع');

    expect($far->id)->not->toBeIn($results->pluck('id')->all());
});

test('the search center comes from the device, then the account, then the city', function () {
    $user = User::factory()->create(['latitude' => 33.4, 'longitude' => 36.1]);

    $request = Request::create('/search', 'GET', ['lat' => '33.55', 'lng' => '36.30']);
    expect(ItemSearch::center($request)['source'])->toBe('device');

    $request = Request::create('/search');
    $request->setUserResolver(fn () => $user);
    expect(ItemSearch::center($request)['lat'])->toBe(33.4);

    expect(ItemSearch::center(Request::create('/search'))['source'])->toBe('default');
});

test('only visible items are searchable', function () {
    logicItem(['title' => 'ظاهر']);
    logicItem(['title' => 'موقوف', 'state' => 'inactive']);
    $blocked = User::factory()->create();
    $blocked->forceFill(['is_blocked' => true])->save();
    logicItem(['title' => 'محظور', 'owner_id' => $blocked->id]);

    expect(Item::visible()->pluck('title')->all())->toBe(['ظاهر']);
});

test('the search api returns nearby items with approximate coordinates and no contact details', function () {
    $this->owner->update(['phone' => '0933999888']);
    logicItem(['title' => 'دراجة جبلية', 'latitude' => 33.505, 'longitude' => 36.255]);

    $this->getJson('/api/items?q=دراجة&lat=33.5032&lng=36.2541')
        ->assertOk()
        ->assertJsonPath('count', 1)
        ->assertJsonPath('items.0.approx_lat', 33.51)
        ->assertDontSee('0933999888');
});

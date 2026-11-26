<?php

use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\User;

beforeEach(function () {
    $category = Category::create(['category_name' => 'أثاث', 'state' => 'active']);
    $condition = Condition::create(['condition_name' => 'جديد', 'order' => 1]);
    $this->owner = User::factory()->create();
    $this->user = User::factory()->create();

    $this->item = Item::create([
        'owner_id' => $this->owner->id,
        'category_id' => $category->id,
        'condition_id' => $condition->id,
        'transaction_type' => 'sale',
        'title' => 'كرسي مكتب',
        'price' => 4500,
        'state' => 'active',
    ]);
});

test('a user can add and remove a favorite with json', function () {
    $this->actingAs($this->user)
        ->postJson("/favorites/{$this->item->id}")
        ->assertOk()
        ->assertJson(['favorited' => true]);

    $this->assertDatabaseHas('wishlists', ['user_id' => $this->user->id, 'item_id' => $this->item->id]);

    $this->actingAs($this->user)
        ->postJson("/favorites/{$this->item->id}")
        ->assertJson(['favorited' => false]);

    $this->assertDatabaseMissing('wishlists', ['user_id' => $this->user->id]);
});

test('owners cannot favorite their own items', function () {
    $this->actingAs($this->owner)
        ->postJson("/favorites/{$this->item->id}")
        ->assertStatus(422);
});

test('unavailable items cannot be added to favorites', function () {
    $this->item->update(['state' => 'sold']);

    $this->actingAs($this->user)
        ->postJson("/favorites/{$this->item->id}")
        ->assertStatus(422);
});

test('guests are sent to the login page', function () {
    $this->post("/favorites/{$this->item->id}")->assertRedirect(route('login'));
});

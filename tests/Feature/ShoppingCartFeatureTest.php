<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingCartFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function client_can_view_cart()
    {
        $client = User::factory()->client()->create();
        ShoppingCart::factory()->count(3)->create(['user_id' => $client->id]);

        $response = $this->actingAs($client)->get('/api/shopping-cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'items',
                    'total',
                    'count',
                    'formatted_total',
                ],
            ]);
    }

    /** @test */
    public function admin_cannot_view_cart()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/api/shopping-cart');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_view_cart()
    {
        ShoppingCart::factory()->count(3)->create();

        $response = $this->get('/api/shopping-cart');

        $response->assertStatus(401);
    }

    /** @test */
    public function client_can_add_item_to_cart()
    {
        $client = User::factory()->client()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'sale_price' => 100.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($client)->post('/api/shopping-cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'cart_item' => [
                        'id',
                        'product_id',
                        'quantity',
                        'unit_price',
                        'total_price',
                    ],
                    'cart_total',
                    'cart_count',
                ],
            ]);

        $this->assertDatabaseHas('shopping_cart', [
            'user_id' => $client->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_price' => 200.00,
        ]);
    }

    /** @test */
    public function client_cannot_add_inactive_product_to_cart()
    {
        $client = User::factory()->client()->create();
        $product = Product::factory()->create(['is_active' => false]);

        $response = $this->actingAs($client)->post('/api/shopping-cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Produto não está disponível',
            ]);
    }

    /** @test */
    public function client_cannot_add_product_with_insufficient_stock()
    {
        $client = User::factory()->client()->create();
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $response = $this->actingAs($client)->post('/api/shopping-cart', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Estoque insuficiente',
            ]);
    }

    /** @test */
    public function it_validates_product_exists()
    {
        $client = User::factory()->client()->create();

        $response = $this->actingAs($client)->post('/api/shopping-cart', [
            'product_id' => 999, // Non-existent product
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    /** @test */
    public function it_validates_quantity_limits()
    {
        $client = User::factory()->client()->create();
        $product = Product::factory()->create();

        // Test minimum quantity
        $response = $this->actingAs($client)->post('/api/shopping-cart', [
            'product_id' => $product->id,
            'quantity' => 0, // Below minimum
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        // Test maximum quantity
        $response = $this->actingAs($client)->post('/api/shopping-cart', [
            'product_id' => $product->id,
            'quantity' => 101, // Above maximum
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function client_can_update_cart_item_quantity()
    {
        $client = User::factory()->client()->create();
        $product = Product::factory()->create(['sale_price' => 50.00]);
        $cartItem = ShoppingCart::factory()->create([
            'user_id' => $client->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($client)->put("/api/shopping-cart/{$cartItem->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'cart_item' => [
                        'id',
                        'quantity',
                        'total_price',
                    ],
                    'cart_total',
                    'cart_count',
                ],
            ]);

        $cartItem->refresh();
        $this->assertEquals(5, $cartItem->quantity);
        $this->assertEquals(250.00, $cartItem->total_price); // 5 * 50
    }

    /** @test */
    public function client_cannot_update_other_user_cart_item()
    {
        $client = User::factory()->client()->create();
        $otherClient = User::factory()->client()->create();
        $cartItem = ShoppingCart::factory()->create(['user_id' => $otherClient->id]);

        $response = $this->actingAs($client)->put("/api/shopping-cart/{$cartItem->id}", [
            'quantity' => 3,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function client_can_remove_cart_item()
    {
        $client = User::factory()->client()->create();
        $cartItem = ShoppingCart::factory()->create(['user_id' => $client->id]);

        $response = $this->actingAs($client)->delete("/api/shopping-cart/{$cartItem->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Item removido do carrinho',
            ]);

        $this->assertDatabaseMissing('shopping_cart', ['id' => $cartItem->id]);
    }

    /** @test */
    public function client_can_clear_cart()
    {
        $client = User::factory()->client()->create();
        ShoppingCart::factory()->count(5)->create(['user_id' => $client->id]);

        $response = $this->actingAs($client)->delete('/api/shopping-cart/clear');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Carrinho limpo',
                'data' => [
                    'cart_total' => 0,
                    'cart_count' => 0,
                ],
            ]);

        $this->assertEquals(0, ShoppingCart::where('user_id', $client->id)->count());
    }

    /** @test */
    public function it_can_calculate_shipping()
    {
        $client = User::factory()->client()->create();
        
        // Add product with weight
        $product = Product::factory()->create(['weight' => 2.5]);
        ShoppingCart::factory()->create([
            'user_id' => $client->id,
            'product_id' => $product->id,
            'quantity' => 2, // Total weight: 5kg
        ]);

        $response = $this->actingAs($client)->post('/api/shopping-cart/shipping');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'weight',
                    'cost',
                    'formatted_cost',
                    'delivery_time',
                ],
            ]);

        $response->assertJsonFragment([
            'success' => true,
            'data' => [
                'weight' => 5.0,
                'cost' => 40.00, // 5kg = R$ 40,00
                'delivery_time' => '3-5 dias úteis',
            ],
        ]);
    }

    /** @test */
    public function it_returns_error_for_empty_cart_shipping()
    {
        $client = User::factory()->client()->create();

        $response = $this->actingAs($client)->post('/api/shopping-cart/shipping');

        $response->assertStatus(400)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Carrinho vazio',
            ]);
    }

    /** @test */
    public function it_can_prepare_checkout()
    {
        $client = User::factory()->client()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
        
        ShoppingCart::factory()->create([
            'user_id' => $client->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($client)->post('/api/shopping-cart/checkout');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'cart' => [
                        'items',
                        'total',
                        'count',
                    ],
                    'items',
                ],
            ]);

        $response->assertJsonFragment([
            'success' => true,
            'message' => 'Checkout preparado com sucesso.',
        ]);
    }

    /** @test */
    public function it_returns_error_for_invalid_cart_checkout()
    {
        $client = User::factory()->client()->create();
        $activeProduct = Product::factory()->create(['is_active' => true]);
        $inactiveProduct = Product::factory()->create(['is_active' => false]);
        
        // Valid item
        ShoppingCart::factory()->create([
            'user_id' => $client->id,
            'product_id' => $activeProduct->id,
            'quantity' => 1,
        ]);

        // Invalid item - inactive product
        ShoppingCart::factory()->create([
            'user_id' => $client->id,
            'product_id' => $inactiveProduct->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($client)->post('/api/shopping-cart/checkout');

        $response->assertStatus(400)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Itens do carrinho inválidos',
            ]);
    }

    /** @test */
    public function it_can_get_cart_summary()
    {
        $client = User::factory()->client()->create();
        
        ShoppingCart::factory()->create([
            'user_id' => $client->id,
            'total_price' => 100.00,
            'quantity' => 2,
        ]);

        ShoppingCart::factory()->create([
            'user_id' => $client->id,
            'total_price' => 50.00,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($client)->get('/api/shopping-cart/summary');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'data' => [
                    'total_items' => 3, // 2 + 1
                    'total_value' => 150.00, // 100 + 50
                    'formatted_total' => 'R$ 150,00',
                    'items_count' => 2,
                ],
            ]);
    }

    /** @test */
    public function it_merges_duplicate_products_in_cart()
    {
        $client = User::factory()->client()->create();
        $product = Product::factory()->create(['sale_price' => 25.00]);

        // Add same product twice
        $this->actingAs($client)->post('/api/shopping-cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($client)->post('/api/shopping-cart', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertStatus(201);

        // Should have one item with quantity 5
        $cartItems = ShoppingCart::where('user_id', $client->id)->get();
        $this->assertCount(1, $cartItems);
        $this->assertEquals(5, $cartItems->first()->quantity);
        $this->assertEquals(125.00, $cartItems->first()->total_price); // 5 * 25
    }
}

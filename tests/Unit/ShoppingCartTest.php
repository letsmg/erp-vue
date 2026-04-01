<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\User;
use App\Repositories\ShoppingCartRepository;
use App\Services\ShoppingCartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingCartTest extends TestCase
{
    use RefreshDatabase;

    private ShoppingCartRepository $repository;
    private ShoppingCartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ShoppingCartRepository();
        $this->service = new ShoppingCartService($this->repository);
    }

    /** @test */
    public function it_can_add_item_to_cart()
    {
        $user = User::factory()->client()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'sale_price' => 100.00,
        ]);

        $result = $this->service->addToCart($user->id, $product->id, 2);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('shopping_cart', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_price' => 200.00,
        ]);
    }

    /** @test */
    public function it_cannot_add_inactive_product_to_cart()
    {
        $user = User::factory()->client()->create();
        $product = Product::factory()->create(['is_active' => false]);

        $result = $this->service->addToCart($user->id, $product->id, 1);

        $this->assertFalse($result['success']);
        $this->assertEquals('Produto não está disponível', $result['message']);
    }

    /** @test */
    public function it_cannot_add_product_with_insufficient_stock()
    {
        $user = User::factory()->client()->create();
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $result = $this->service->addToCart($user->id, $product->id, 10);

        $this->assertFalse($result['success']);
        $this->assertEquals('Estoque insuficiente', $result['message']);
        $this->assertEquals(5, $result['available']);
    }

    /** @test */
    public function it_updates_existing_item_quantity()
    {
        $user = User::factory()->client()->create();
        $product = Product::factory()->create(['sale_price' => 50.00]);

        // Add first item
        $this->service->addToCart($user->id, $product->id, 2);

        // Add same product again
        $result = $this->service->addToCart($user->id, $product->id, 3);

        $this->assertTrue($result['success']);
        
        $cartItem = ShoppingCart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertEquals(5, $cartItem->quantity); // 2 + 3
        $this->assertEquals(250.00, $cartItem->total_price); // 5 * 50
    }

    /** @test */
    public function it_can_update_quantity()
    {
        $user = User::factory()->client()->create();
        $product = Product::factory()->create(['sale_price' => 75.00]);
        $cartItem = ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 75.00,
            'total_price' => 150.00,
        ]);

        $result = $this->service->updateQuantity($user->id, $cartItem->id, 5);

        $this->assertTrue($result['success']);
        
        $cartItem->refresh();
        $this->assertEquals(5, $cartItem->quantity);
        $this->assertEquals(375.00, $cartItem->total_price); // 5 * 75
    }

    /** @test */
    public function it_removes_item_when_quantity_is_zero()
    {
        $user = User::factory()->client()->create();
        $cartItem = ShoppingCart::factory()->create(['user_id' => $user->id]);

        $result = $this->service->updateQuantity($user->id, $cartItem->id, 0);

        $this->assertTrue($result['success']);
        $this->assertDatabaseMissing('shopping_cart', ['id' => $cartItem->id]);
    }

    /** @test */
    public function it_can_remove_item()
    {
        $user = User::factory()->client()->create();
        $cartItem = ShoppingCart::factory()->create(['user_id' => $user->id]);

        $result = $this->service->removeFromCart($user->id, $cartItem->id);

        $this->assertTrue($result['success']);
        $this->assertDatabaseMissing('shopping_cart', ['id' => $cartItem->id]);
    }

    /** @test */
    public function it_can_clear_cart()
    {
        $user = User::factory()->client()->create();
        
        // Add multiple items
        ShoppingCart::factory()->count(3)->create(['user_id' => $user->id]);

        $result = $this->service->clearCart($user->id);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, ShoppingCart::where('user_id', $user->id)->count());
    }

    /** @test */
    public function it_calculates_cart_total()
    {
        $user = User::factory()->client()->create();
        
        ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'total_price' => 100.00,
        ]);

        ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'total_price' => 50.00,
        ]);

        $total = $this->repository->getCartTotal($user->id);
        $this->assertEquals(150.00, $total);
    }

    /** @test */
    public function it_calculates_cart_item_count()
    {
        $user = User::factory()->client()->create();
        
        ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'quantity' => 2,
        ]);

        ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'quantity' => 3,
        ]);

        $count = $this->repository->getCartItemCount($user->id);
        $this->assertEquals(5, $count); // 2 + 3
    }

    /** @test */
    public function it_checks_if_product_is_in_cart()
    {
        $user = User::factory()->client()->create();
        $product = Product::factory()->create();
        
        ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertTrue($this->repository->isProductInCart($user->id, $product->id));
        $this->assertFalse($this->repository->isProductInCart($user->id, 999));
    }

    /** @test */
    public function it_removes_inactive_products()
    {
        $user = User::factory()->client()->create();
        $activeProduct = Product::factory()->create(['is_active' => true]);
        $inactiveProduct = Product::factory()->create(['is_active' => false]);
        
        ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $activeProduct->id,
        ]);

        ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $inactiveProduct->id,
        ]);

        $removedCount = $this->repository->removeInactiveProducts($user->id);
        $this->assertEquals(1, $removedCount);

        $this->assertDatabaseHas('shopping_cart', [
            'user_id' => $user->id,
            'product_id' => $activeProduct->id,
        ]);

        $this->assertDatabaseMissing('shopping_cart', [
            'user_id' => $user->id,
            'product_id' => $inactiveProduct->id,
        ]);
    }

    /** @test */
    public function it_updates_cart_prices()
    {
        $user = User::factory()->client()->create();
        $product = Product::factory()->create([
            'sale_price' => 100.00,
        ]);
        
        $cartItem = ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'unit_price' => 80.00, // Preço antigo
            'quantity' => 2,
        ]);

        // Update product price
        $product->update(['sale_price' => 120.00]);

        $updatedCount = $this->repository->updateCartPrices($user->id);
        $this->assertEquals(1, $updatedCount);

        $cartItem->refresh();
        $this->assertEquals(120.00, $cartItem->unit_price);
        $this->assertEquals(240.00, $cartItem->total_price); // 2 * 120
    }

    /** @test */
    public function it_validates_cart_for_checkout()
    {
        $user = User::factory()->client()->create();
        $activeProduct = Product::factory()->create([
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
        
        $inactiveProduct = Product::factory()->create(['is_active' => false]);
        
        // Valid item
        ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $activeProduct->id,
            'quantity' => 5,
        ]);

        // Invalid item - inactive product
        ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $inactiveProduct->id,
            'quantity' => 1,
        ]);

        $validation = $this->service->validateCartForCheckout($user->id);

        $this->assertFalse($validation['valid']);
        $this->assertCount(1, $validation['issues']);
        $this->assertStringContainsString('não está disponível', $validation['issues'][0]['message']);
    }

    /** @test */
    public function it_calculates_shipping()
    {
        $user = User::factory()->client()->create();
        
        // Add products with different weights
        ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'product_id' => Product::factory()->create(['weight' => 0.5])->id,
            'quantity' => 2, // Total weight: 1kg
        ]);

        $result = $this->service->calculateShipping($user->id);

        $this->assertTrue($result['success']);
        $this->assertEquals(1.0, $result['weight']);
        $this->assertEquals(15.00, $result['cost']); // 1kg = R$ 15,00
        $this->assertEquals('R$ 15,00', $result['formatted_cost']);
        $this->assertEquals('1-2 dias úteis', $result['delivery_time']);
    }

    /** @test */
    public function it_prepares_checkout_data()
    {
        $user = User::factory()->client()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
        
        ShoppingCart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $result = $this->service->prepareCheckoutData($user->id);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('cart', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
    }
}

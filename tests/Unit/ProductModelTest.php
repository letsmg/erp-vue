<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_has_required_fields()
    {
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => $product->title,
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
            'description' => $product->description,
            'brand' => $product->brand,
            'cost_price' => $product->cost_price,
            'sale_price' => $product->sale_price,
            'stock_quantity' => $product->stock_quantity,
            'is_active' => $product->is_active,
            'slug' => $product->slug,
        ]);
    }

    public function test_product_generates_slug_automatically()
    {
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
            'title' => 'Produto Teste Exemplo',
        ]);

        $this->assertEquals('produto-teste-exemplo', $product->slug);
    }

    public function test_product_slug_is_unique()
    {
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create();

        $product1 = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
            'title' => 'Produto Teste',
        ]);

        $product2 = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
            'title' => 'Produto Teste',
        ]);

        $this->assertNotEquals($product1->slug, $product2->slug);
    }

    public function test_product_casts_is_active_to_boolean()
    {
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->assertIsBool($product->is_active);
        $this->assertTrue($product->is_active);
    }

    public function test_product_casts_free_shipping_to_boolean()
    {
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
            'free_shipping' => true,
        ]);

        $this->assertIsBool($product->free_shipping);
        $this->assertTrue($product->free_shipping);
    }

    public function test_product_current_price_returns_promo_price_when_active()
    {
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
            'sale_price' => 100,
            'promo_price' => 80,
            'promo_start_at' => now()->subDay(),
            'promo_end_at' => now()->addDay(),
        ]);

        $this->assertEquals(80, $product->current_price);
    }

    public function test_product_current_price_returns_sale_price_when_promo_inactive()
    {
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
            'sale_price' => 100,
            'promo_price' => 80,
            'promo_start_at' => now()->subDays(5),
            'promo_end_at' => now()->subDays(2),
        ]);

        $this->assertEquals(100, $product->current_price);
    }

    public function test_product_belongs_to_supplier()
    {
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
        ]);

        $this->assertInstanceOf(Supplier::class, $product->supplier);
        $this->assertEquals($supplier->id, $product->supplier_id);
    }

    public function test_product_belongs_to_category()
    {
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
        ]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category_id);
    }

    public function test_product_has_many_images()
    {
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->images());
    }
}

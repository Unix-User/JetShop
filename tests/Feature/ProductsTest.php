<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;

class ProductsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test product index page.
     */
    public function test_products_index(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/products');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Products'));
    }

    /**
     * Test product creation.
     */
    public function test_create_product(): void
    {
        $user = User::factory()->create();
        $productData = [
            'name' => 'New Product',
            'price' => 99.99,
            'description' => 'A brand new product'
        ];

        $response = $this->actingAs($user)->post('/products', $productData);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', $productData);
    }

    /**
     * Test product update.
     */
    public function test_update_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['team_id' => $user->currentTeam->id]);

        $updatedData = [
            'name' => 'Updated Product',
            'price' => 199.99,
            'description' => 'An updated product description'
        ];

        $response = $this->actingAs($user)->put("/products/{$product->id}", $updatedData);

        $response->assertRedirect("/products/{$product->id}");
        $this->assertDatabaseHas('products', $updatedData);
    }

    /**
     * Test product deletion.
     */
    public function test_delete_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['team_id' => $user->currentTeam->id]);

        $response = $this->actingAs($user)->delete("/products/{$product->id}");

        $response->assertRedirect('/products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}

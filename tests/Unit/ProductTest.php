<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\TestCase; // Changed from PHPUnit\Framework\TestCase
use App\Models\Product;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase; // Added for database state refresh
use App\Models\User; // Required for using User factory

class ProductTest extends TestCase
{
    use RefreshDatabase; // Ensures each test is run with a fresh database state

    protected $productController;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(); // Using a factory to create a test user
        Auth::login($this->user); // Authenticating the created user
        $this->productController = new ProductController();
    }

    public function testProductIndex()
    {
        $response = $this->productController->index();
        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('products', $response->original->getData());
    }

    public function testProductStore()
    {
        $request = Request::create('/store', 'POST', [
            'name' => 'Test Product',
            'price' => 100,
            'description' => 'A test product'
        ]);

        $response = $this->productController->store($request);
        $this->assertEquals(302, $response->status()); // Expecting a redirect
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 100,
            'description' => 'A test product'
        ]);
    }

    public function testProductUpdate()
    {
        $product = Product::create([
            'name' => 'Old Product',
            'price' => 50,
            'description' => 'An old product',
            'team_id' => $this->user->currentTeam->id
        ]);

        $request = Request::create('/update', 'PUT', [
            'name' => 'Updated Product',
            'price' => 150,
            'description' => 'An updated product'
        ]);

        $response = $this->productController->update($request, $product);
        $this->assertEquals(302, $response->status()); // Expecting a redirect
        $this->assertDatabaseHas('products', [
            'name' => 'Updated Product',
            'price' => 150,
            'description' => 'An updated product'
        ]);
    }

    public function testProductDestroy()
    {
        $product = Product::create([
            'name' => 'Product to Delete',
            'price' => 70,
            'description' => 'A product to be deleted',
            'team_id' => $this->user->currentTeam->id
        ]);

        $response = $this->productController->destroy($product);
        $this->assertEquals(302, $response->status()); // Expecting a redirect
        $this->assertDatabaseMissing('products', [
            'name' => 'Product to Delete'
        ]);
    }
}
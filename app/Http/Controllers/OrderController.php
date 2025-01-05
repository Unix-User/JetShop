<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $orders = Order::where('team_id', $user->currentTeam->id)
                           ->paginate($request->input('per_page', 5)); // Default to 15 items per page if 'per_page' is not specified
        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Validating the incoming request data to ensure it meets the expected format and types
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id', // Ensure the client_id is provided and exists in the clients table
            'total_price' => 'required|numeric', // Ensure total_price is provided and is a numeric value
            'products' => 'required|array', // Ensure products is provided and is an array
            'products.*.id' => 'required|exists:products,id', // Ensure each product id exists in the products table
            'products.*.quantity' => 'required|numeric|min:1', // Ensure each product quantity is numeric and at least 1
            'products.*.price' => 'required|numeric|min:0' // Ensure each product price is numeric and non-negative
        ]);

        $order = new Order();
        $order->fill([
            'client_id' => $validated['client_id'],
            'total_price' => $validated['total_price'],
            'user_id' => $user->id,
            'team_id' => $user->currentTeam->id
        ]);
        $order->save();

        // Collecting products from the request and attaching them to the order
        $products = $request->input('products');
        foreach ($products as $product) {
            // Attach each product to the order with additional details
            $order->products()->attach($product['id'], [
                'quantity' => $product['quantity'],
                'price' => $product['price']
            ]);
            // Create a sales record for each product
            $order->sales()->create([
                'product_id' => $product['id'],
                'quantity' => $product['quantity'],
                'total_price' => $product['price'] * $product['quantity'],
                'team_id' => $user->currentTeam->id
            ]);
        }

        // Redirect to the orders index route after successful creation
        return redirect()->route('orders.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'total_price' => 'required|numeric',
            'products' => 'required|array',
            'products.*' => 'exists:products,id'
        ]);

        $order = Order::where('team_id', $user->currentTeam->id)->findOrFail($id);
        $order->fill($validated);
        $order->save();

        $order->products()->sync($request->input('products'));

        return response()->json(['message' => 'Order updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $order = Order::where('team_id', $user->currentTeam->id)->findOrFail($id);
        $order->products()->detach();
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
}
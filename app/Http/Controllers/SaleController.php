<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $sales = Sale::with(['product', 'order', 'team'])->where('team_id', $user->currentTeam->id)->paginate($request->input('per_page', 15));
        return response()->json($sales);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validating the request data to ensure it meets the expected format and types
        $validated = $request->validate([
            'order_id' => 'nullable|exists:orders,id', // Ensure the order_id exists in the orders table if provided
            'product_id' => 'required|exists:products,id', // Ensure the product_id is provided and exists in the products table
            'quantity' => 'required|integer|min:1', // Ensure quantity is provided, is an integer, and at least 1
            'total_price' => 'required|numeric' // Ensure total_price is provided and is a numeric value
        ]);

        // Check if order_id is provided, if not, create a new order
        if (empty($validated['order_id'])) {
            $order = new Order();
            $order->user_id = $user->id;
            $order->team_id = $user->currentTeam->id;
            $order->save();
            $validated['order_id'] = $order->id; // Assign the newly created order's id to the validated data
        }

        // Creating a new Sale instance with validated data
        $sale = new Sale();
        $sale->fill($validated);
        $sale->team_id = $user->currentTeam->id; // Assigning the team_id from the authenticated user's current team

        // Saving the new Sale instance to the database
        $sale->save();

        // Redirecting to the sales index route after successful creation
        return redirect()->route('sales.index');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric'
        ]);

        $sale = Sale::where('team_id', $user->currentTeam->id)->findOrFail($id);
        $sale->fill($validated);
        $sale->save();

        return response()->json(['message' => 'Sale updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $sale = Sale::where('team_id', $user->currentTeam->id)->findOrFail($id);
        $sale->delete();

        return response()->json(['message' => 'Sale deleted successfully'], 200);
    }
}


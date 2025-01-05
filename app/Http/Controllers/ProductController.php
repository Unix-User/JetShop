<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $products = Product::where('team_id', $user->currentTeam->id)
                           ->paginate($request->input('per_page', 5)); // Default to 15 items per page if 'per_page' is not specified
        return response()->json($products);
    }
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'required|string'
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->team_id = $user->currentTeam->id;
        $product->save();

        return redirect()->route('products.index');
    }

    public function update(Request $request, Product $product)
    {
        // $user = Auth::user();
        // if (!$user->teams->contains($product->team) || !$user->teams->hasTeamPermission($product->team, 'product:update')) {
        //     abort(403);
        // }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'required|string'
        ]);

        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        return response()->json(['message' => 'Product updated successfully'], 200);
    }

    public function destroy(Product $product)
    {
        // $user = Auth::user();
        // if (!$user->teams->contains($product->team) || !$user->teams->hasTeamPermission($product->team, 'product:delete')) {
        //     abort(403);
        // }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}

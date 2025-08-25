<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductSearchController extends Controller
{
    /**
     * Search products by name and/or price range
     * GET /api/products/search?name=keyword&min_price=10&max_price=50
     */
    public function search(Request $request)
    {
        $query = Product::with('category');

        // Filter by name
        if ($request->has('name')) {
            $name = $request->input('name');
            $query->where('name', 'like', "%{$name}%");
        }

        // Filter by minimum price
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        // Filter by maximum price
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        $products = $query->get();

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }
}

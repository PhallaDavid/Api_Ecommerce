<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::with('category')->get());
    }

    public function show($id)
    {
        return response()->json(Product::with('category')->findOrFail($id));
    }

    public function productsByCategory($categoryId)
    {
        $products = Product::with('category')
            ->where('category_id', $categoryId)
            ->get();

        return response()->json($products);
    }
public function promotion()
{
    $now = now();

    $products = Product::whereNotNull('promotion_start')
        ->whereNotNull('promotion_end')
        ->where('promotion_start', '<=', $now)
        ->where('promotion_end', '>=', $now)
        ->with('category')
        ->get();

    return response()->json($products);
}


public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'sale_price' => 'nullable|numeric',
        'stock' => 'required|integer',
        'category_id' => 'nullable|exists:categories,id',
        'images.*' => 'sometimes|image|max:2048',
        'sku' => 'nullable|string',
        'barcode' => 'nullable|string',
        'featured' => 'nullable|boolean',
        'is_active' => 'nullable|boolean',
        'weight' => 'nullable|numeric',
        'length' => 'nullable|numeric',
        'width' => 'nullable|numeric',
        'height' => 'nullable|numeric',
        'rating' => 'nullable|numeric',
        'sold_count' => 'nullable|integer',
        'promotion_start' => 'nullable|date',
        'promotion_end' => 'nullable|date|after_or_equal:promotion_start',
    ]);

    $imagePaths = [];
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $path = $image->store('products', 'public');
            $imagePaths[] = Storage::url($path);
        }
    }

    // Generate a unique slug
    $slug = Str::slug($request->name);
    $originalSlug = $slug;
    $counter = 1;
    while (Product::where('slug', $slug)->exists()) {
        $slug = $originalSlug . '-' . $counter++;
    }

    $product = Product::create([
        'name' => $request->name,
        'slug' => $slug,
        'description' => $request->description,
        'price' => $request->price,
        'sale_price' => $request->sale_price,
        'stock' => $request->stock,
        'category_id' => $request->category_id,
        'images' => $imagePaths,
        'sku' => $request->sku,
        'barcode' => $request->barcode,
        'featured' => $request->featured ?? 0,
        'is_active' => $request->is_active ?? 1,
        'weight' => $request->weight,
        'length' => $request->length,
        'width' => $request->width,
        'height' => $request->height,
        'rating' => $request->rating ?? 0,
        'sold_count' => $request->sold_count ?? 0,
        'promotion_start' => $request->promotion_start,
        'promotion_end' => $request->promotion_end,
    ]);

    // Load category relation
    $product->load('category');

    return response()->json([
        'message' => 'Product created successfully',
        'product' => $product
    ], 201);
}



    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric',
            'stock' => 'sometimes|required|integer',
            'category_id' => 'nullable|exists:categories,id',
            'images.*' => 'sometimes|image|max:2048',
            'promotion_start' => 'nullable|date',
            'promotion_end' => 'nullable|date|after_or_equal:promotion_start',
        ]);

        $imagePaths = $product->images ?? [];
        if ($request->hasFile('images')) {
            foreach ($imagePaths as $old) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $old));
            }
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = Storage::url($path);
            }
        }

        $product->update([
            'name' => $request->name ?? $product->name,
            'slug' => $request->name ? Str::slug($request->name) : $product->slug,
            'description' => $request->description ?? $product->description,
            'price' => $request->price ?? $product->price,
            'stock' => $request->stock ?? $product->stock,
            'category_id' => $request->category_id ?? $product->category_id,
            'images' => $imagePaths,
            'promotion_start' => $request->promotion_start ?? $product->promotion_start,
            'promotion_end' => $request->promotion_end ?? $product->promotion_end,
        ]);

        return response()->json($product);
    }
    public function addFavorite($productId)
    {
        $favorite = Favorite::updateOrCreate([
            'user_id' => Auth::id(),
            'product_id' => $productId,
        ]);

        return response()->json(['message' => 'Added to favorites', 'favorite' => $favorite]);
    }

    public function favorites(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favorites()->with('product')->get();
        return response()->json($favorites);
    }

    // Remove from favorite
    public function removeFavorite($productId)
    {
        Favorite::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->delete();

        return response()->json(['message' => 'Removed from favorites']);
    }

    // Add to cart
    public function addToCart(Request $request, $productId)
    {
        $cart = Cart::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $productId],
            ['quantity' => $request->quantity ?? 1]
        );

        return response()->json(['message' => 'Added to cart', 'cart' => $cart]);
    }

    public function cart(Request $request)
    {
        $user = $request->user();
        $cartItems = $user->cart()->with('product')->get();
        return response()->json($cartItems);
    }

    // Remove from cart
    public function removeFromCart($productId)
    {
        Cart::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->delete();

        return response()->json(['message' => 'Removed from cart']);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product->images) {
            foreach ($product->images as $img) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $img));
            }
        }
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Get reviews for a product
    public function index($productId)
    {
        $reviews = Review::where('product_id', $productId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($reviews);
    }
    public function store(Request $request, $productId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $user = $request->user(); // authenticated user

        $review = Review::create([
            'product_id' => $productId,
            'user_id' => $user->id,
            'comment' => $request->comment,
            'rating' => $request->rating,
        ]);

        return response()->json([
            'id' => $review->id,
            'user' => ['id' => $user->id, 'name' => $user->name],
            'comment' => $review->comment,
            'rating' => $review->rating,
            'created_at' => $review->created_at,
        ], 201);
    }
}

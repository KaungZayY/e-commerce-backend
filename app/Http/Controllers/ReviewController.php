<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index(Request $req, Product $product)
    {
        $perPage = $req->input('per_page', 5);
        $reviews = $product->reviews()->with('user')->orderBy('created_at', 'desc')->paginate($perPage);
        return response()->json($reviews, 200);
    }
    public function store(Request $req, Product $product)
    {
        $data = $req->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        $data['user_id'] = Auth::id();
        $data['product_id'] = $product->id;

        $review = $product->reviews()->create($data);
        return response()->json([
            'review' => $review
        ], 201);
    }

    public function show(Product $product, Review $review)
    {
        return response()->json([
            'review' => $review
        ], 200);
    }

    public function update(Request $req,Product $product, Review $review)
    {
        $data = $req->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        $review->update($data);
        return response()->json([
            'review' => $review
        ], 200);
    }

    public function destroy(Product $product, Review $review)
    {
        $review->delete();
        return response()->json([
            'message' => 'Review deleted successfully.'
        ], 200);
    }
}

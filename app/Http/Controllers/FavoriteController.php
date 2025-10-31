<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    public function index(Request $req)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $perPage = $req->input('perPage');

        $query = Favorite::with('product.category')
            ->where('user_id', $user->id)
            ->latest();

        if ($perPage) {
            $favorites = $query->paginate($perPage);
            $favorites->getCollection()->transform(function ($favorite) {
                $product = $favorite->product;

                if ($product && $product->images) {
                    $product->images = json_decode($product->images, true);
                }

                return $product;
            });
        } else {
            $favorites = $query->get()->pluck('product')->map(function ($product) {
                if ($product && $product->images) {
                    $product->images = json_decode($product->images, true);
                }
                return $product;
            });
        }

        return response()->json([
            'products' => $favorites,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = Auth::user();

        $exists = Favorite::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already in favorites'], 200);
        }

        Favorite::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json(['message' => 'Added to favorites'], 201);
    }

    public function destroy($productId)
    {
        $user = Auth::user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$favorite) {
            return response()->json(['message' => 'Not found in favorites'], 404);
        }

        $favorite->delete();

        return response()->json(['message' => 'Removed from favorites'], 200);
    }
}

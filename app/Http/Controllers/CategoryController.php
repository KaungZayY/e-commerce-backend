<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function category_dropdown()
    {
        $parents = Category::whereNull('parent_id')->select('id', 'category_name')->get();
        return response()->json(['data' => $parents], 200);
    }

    public function sub_category_dropdown($parentId)
    {
        $children = Category::where('parent_id', $parentId)->select('id', 'category_name')->get();
        return response()->json(['data' => $children], 200);
    }

    public function products(Request $req, Category $category, Product $product)
    {
        $perPage = $req->input('perPage', 10);

        // Get all products in this category (including optional subcategories)
        $query = Product::with(['category', 'created_by_user'])
            ->where('category_id', $category->id)
            ->where('id', '!=', $product->id)
            ->latest();

        // Paginate results
        $products = $query->paginate($perPage);

        // Decode images and add is_favorited flag (optional)
        $products->getCollection()->transform(function ($product) {
            $product->images = $product->images ? json_decode($product->images, true) : [];
            return $product;
        });

        return response()->json([
            'products' => $products
        ]);
    }
}

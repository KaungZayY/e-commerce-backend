<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $req)
    {
        $perPage = $req->input('perPage') ?? null;
        $filters = $req->only(['product_name', 'price_range']);

        // Decode price_range if it's a JSON string
        if (!empty($filters['price_range']) && is_string($filters['price_range'])) {
            $filters['price_range'] = json_decode($filters['price_range'], true);
        }

        $query = Product::filter($filters)
            ->orderBy('created_at', 'desc')
            ->with('created_by_user');

        $data = $perPage ? $query->paginate($perPage) : $query->get();

        // Decode images JSON for each product
        $data->getCollection()->transform(function ($product) {
            $product->images = $product->images ? json_decode($product->images, true) : [];
            return $product;
        });

        return response()->json([
            'products' => $data
        ]);
    }

    public function store(ProductRequest $req)
    {
        $data = $req->validated();
        $data['created_by'] = Auth::user()->id;
        if (isset($data['images']) && !is_string($data['images'])) {
            $data['images'] = json_encode($data['images']);
        }
        $product = Product::create($data);
        return response()->json([
            'product' => $product,
        ], 201);
    }

    public function show(Product $product)
    {
        $product->load('category');
        $productArray = $product->toArray();
        $productArray['images'] = $product->images ? json_decode($product->images, true) : [];

        return response()->json([
            'product' => $productArray,
        ], 200);
    }

    public function update(ProductRequest $req, Product $product)
    {
        $data = $req->validated();

        if (isset($data['images']) && !is_string($data['images'])) {
            $data['images'] = json_encode($data['images']);
        }

        $product->update($data);

        return response()->json([
            'product' => $product,
        ], 200);
    }

    public function destroy(Product $product)
    {
        if ($product->images) {
            $images = json_decode($product->images, true); // decode JSON to array
            foreach ($images as $imagePath) {
                if ($imagePath && Storage::exists($imagePath)) {
                    Storage::delete($imagePath);
                }
            }
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.'
        ], 200);
    }
}

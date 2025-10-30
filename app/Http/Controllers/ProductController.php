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

        // Build filters array
        $filters = [
            'product_name' => $req->input('product_name'),
            'category_id'  => $req->input('category_id'),
            'is_popular'   => $req->input('is_popular'),
            'on_sale'      => $req->input('on_sale'),
            'in_stock'     => $req->input('in_stock'),
            'on_backorder' => $req->input('on_backorder'),
        ];

        // Handle price_range properly
        if ($req->has('price_range')) {
            $priceRange = $req->input('price_range');

            // If it's already an array (from query params like price_range[start])
            if (is_array($priceRange)) {
                $filters['price_range'] = [
                    'start' => $priceRange['start'] ?? null,
                    'end' => $priceRange['end'] ?? null,
                ];
            }
            // If it's a JSON string
            elseif (is_string($priceRange)) {
                $filters['price_range'] = json_decode($priceRange, true);
            }
        }

        $query = Product::filter($filters)
            ->with('category')
            ->with('created_by_user');

        $sort = $req->input('sort', 'created_at_desc'); // default sort
        switch ($sort) {
            case 'price_asc': // price sorting considering discounts
                $query->orderByRaw("
            CASE 
                WHEN discount_type IS NULL OR discount_amount IS NULL OR discount_amount = 0 
                    THEN price
                WHEN discount_type = 'amount' 
                    THEN price - discount_amount
                WHEN discount_type = 'percentage' 
                    THEN price - (price * discount_amount / 100)
                ELSE price
            END ASC
        ");
                break;

            case 'price_desc':
                $query->orderByRaw("
            CASE 
                WHEN discount_type IS NULL OR discount_amount IS NULL OR discount_amount = 0 
                    THEN price
                WHEN discount_type = 'amount' 
                    THEN price - discount_amount
                WHEN discount_type = 'percentage' 
                    THEN price - (price * discount_amount / 100)
                ELSE price
            END DESC
        ");
                break;
            case 'is_popular':
                $query->orderBy('is_popular', 'desc');
                break;
            case 'created_at_desc':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'asc');
                break;
        }


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

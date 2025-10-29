<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

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

        return response()->json([
            'products' => $data
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $req)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $perPage = $req->input('perPage');

        $query = Order::with('products')
            ->where('user_id', $user->id)
            ->latest();

        if ($perPage) {
            $orders = $query->paginate($perPage);
        } else {
            $orders = $query->get();
        }

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function store(OrderRequest $req)
    {
        try {
            DB::beginTransaction();

            $data = $req->validated();
            $data['user_id'] = auth('sanctum')->id();
            $order = Order::create($data);
            $order->update(['generated_id' => $this->generateOrderId()]);

            $items = $data['cart'];
            foreach ($items as $item) {
                // Check stock availability first
                $product = Product::find($item['product_id']);

                if (!$product) {
                    return response()->json([
                        'message' => 'Product with name ' . $product->product_name . ' not found',
                    ], 404);
                }

                if ($product->qty < $item['quantity']) {
                    return response()->json([
                        'message' => "Insufficient stock for product '" . $product->product_name ."'",
                    ], 400);
                }

                // Attach the product to the order
                $order->products()->attach($item['product_id'], [
                    'qty' => $item['quantity'],
                    'final_unit_price' => $item['final_unit_price'],
                    'subtotal' => $item['quantity'] * $item['final_unit_price'],
                ]);

                // Reduce the product quantity
                $product->decrement('qty', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'order' => $order->load('products'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Order creation failed',
                // 'error' => $e->getMessage(),
            ], 400);
        }
    }

    protected function generateOrderId()
    {
        $latestRecord = Order::latest('id')->first();
        $nextNumber = $latestRecord ? $latestRecord->id + 1 : 1;
        $customId = 'ORD-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
        return $customId;
    }
}

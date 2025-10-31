<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use Illuminate\Http\Request;

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
        $data = $req->validated();
        $data['user_id'] = auth('sanctum')->id();
        $order = Order::create($data);
        $order->update(['generated_id' => $this->generateOrderId()]);

        $items = $data['cart'];
        foreach ($items as $item) {
            $order->products()->attach($item['product_id'], [
                'qty' => $item['quantity'],
                'final_unit_price' => $item['final_unit_price'],
                'subtotal' => $item['quantity'] * $item['final_unit_price'],
            ]);
        }
        return response()->json([
            'order' => $order->load('products'),
        ], 201);
    }

    protected function generateOrderId()
    {
        $latestRecord = Order::latest('id')->first();
        $nextNumber = $latestRecord ? $latestRecord->id + 1 : 1;
        $customId = 'ORD-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
        return $customId;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Helpers\Telegram;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        $total = 0;
        $orderItems = [];

        foreach ($request->products as $item) {
            $product = Product::findOrFail($item['id']);
            $price = $product->sale_price ?? $product->price;
            $total += $price * $item['quantity'];

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $price,
            ];
        }

        $order = Order::create([
            'user_id' => $user->id,
            'total' => $total,
            'status' => 'pending',
        ]);

        foreach ($orderItems as $item) {
            $order->items()->create($item);
        }
        $message = "<b>🛒 New Order #{$order->id}</b>\n";
        $message .= "<b>Payment Method:</b> {$request->payment_method}\n";
        $message .= "<b>User:</b> {$user->name} ({$user->email})\n";
        $message .= "<b>Total:</b> $total\n";
        $message .= "<b>Items:</b>\n";
        foreach ($order->items as $item) {
            $message .= "• {$item->name} x{$item->quantity} - {$item->price}\n";
        }
        foreach ($order->items as $item) {
            $message .= "- {$item->product->name} x {$item->quantity} = {$item->price}\n";
        }
        Telegram::sendMessage(env('TELEGRAM_CHAT_ID'), $message);

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order->load('items.product')
        ], 201);
    }
    public function history(Request $request)
    {
        $user = $request->user();

        // Get all orders with their items and product details
        $orders = Order::where('user_id', $user->id)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Order history fetched successfully',
            'orders' => $orders
        ], 200);
    }
    public function getOrderById($id)
{
    $user = auth()->user();

    $order = Order::with('items.product')
        ->where('user_id', $user->id) // make sure user can only see their own orders
        ->find($id);

    if (!$order) {
        return response()->json([
            'status' => false,
            'message' => 'Order not found',
        ], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'Order fetched successfully',
        'order' => $order
    ]);
}

}

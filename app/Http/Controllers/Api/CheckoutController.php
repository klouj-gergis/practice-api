<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;

class CheckoutController extends Controller
{
    //
    public function checkout(Request $request){
        $data = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_zipcode' => 'required|string|max:20',
            'shipping_country' => 'required|string|max:20',
            'shipping_phone' => 'required|string|max:20',
            'payment_method' => 'required|in:credit_card,paypal',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();

        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();

        if($cartItems->isEmpty()){
            return response()->json([
                'message' => 'Your Cart is empty'
            ], 400);
        }


        $subtotal = 0;
        $items = [];
        foreach ($cartItems as $item){
            $product = $item->product;

            if($product->stock < $item->quantity){
                return response()->json([
                    'message' => "product {$product->name} is out of stock",
                ], 400);
            }

            $itemSubtotal = round($product->price * $item->quantity, 2);
            $subtotal += $itemSubtotal;

            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $item->quantity,
                'price' => $product->price,
                'subtotal' => $itemSubtotal,
            ];
        }

        $tax = round($subtotal * 0.08, 2); // assume that tax is 8%
        $shippingCost = 5.00;
        $total = round($subtotal + $tax + $shippingCost, 2);

        DB::beginTransaction();
        try{
            $order = new Order([
                'user_id' => $user->id,
                'status' => 'pending',
                'shipping_name' => $request->shipping_name ,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_state' => $request->shipping_state,
                'shipping_zipcode' => $request->shipping_zipcode,
                'shipping_country' => $request->shipping_country,
                'shipping_phone' => $request->shipping_phone,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'payment_method' => $request->payment_method ?? 'cod',// cod = cash on deleviry
                'payment_status' => 'pending',
                'order_number' => Order::generateOrderNumber(),
                'notes' => $request->notes,
            ]);

            $user->orders()->save($order);
            foreach($items as $item){
                $order->itmes()->create($item);
                Product::where('id', $item['product_id'])->decrement('stock', $item['quantity']);
            }

            Cart::where('user_id', $user->id)->each(function ($cartItem){
                $cartItem->delete();
            });
            DB::commit();
            return response()->json([
                'message' => 'order placed successfully',
                'success' => true,
                'order' => $order->load('items')
            ], 201);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'message' => "faild to place order: {$e->getMessage()}"
            ], 400);
        }
    }

    public function orderHestory(Request $request){
        $user = $request->user();
        $orders = $user->orders()->with('items')->get();

        return response()->json([
            'message' => 'orders retrieved successfully',
            'success' => true,
            'orders' => $orders
        ], 200);
    }

    public function showOrder(Request $request, $id){
        $user = $request->user();
        $order = $user->orders()->with('items')->find($id);
        if(!$order){
            return response()->json([
                'message' => 'orders not found',
                'success' => false
            ], 404);
        }

        return response()->json([
            'message' => 'order retrieved successfully',
            'success' => true,
            'orders' => $order
        ], 200);
    }
}

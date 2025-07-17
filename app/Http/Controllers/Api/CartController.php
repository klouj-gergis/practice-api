<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;


class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $user = $request->user();

        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();
        $total = $cartItems->sum(function($item){
            return $item->product->price * $item->quantity;
        });

        return response()->json([
            'message' => 'Cart items retrieved successfully',
            'success' => true,
            'cart' => $cartItems,
            'total' => $total
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $user = $request->user();
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);
        $cartItem = Cart::where('user_id', $user->id);
        if($cartItem){
            $cartItem->quantity += $data['quantity'];
            $cartItem->save();

            return response()->json([
                'message' => 'Cart item updated successfully',
                'success' => true,
                'cart_item' => $cartItem
            ], 200);
        }else {
            $cartItem = Cart::create([
                'user_id' => $user->id,
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity']
            ]);

            return response()->json([
                'message' => 'Cart item added successfully',
                'success' => true,
                'cart_item' => $cartItem
            ], 201);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cart $cart)
    {
        //
        $data = $request->validate([
            'quantity' => 'required|'
        ]);

        $cart->quantity = $data['quantity'];
        $cart->save();

        return response()->json([
            'message' => 'cart item updated successfully',
            'success' => true,
            'cart_item' => $cart
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart)
    {
        //
        $cart->delete();
        return response()->json([
            'message' => 'cart item deleted successfully',
            'success' => true,
        ], 200);
    }

    public function clear(Request $request)
    {
        $user = $request->user();

        Cart::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'cart cleared successfully',
            'success' => true,
        ], 200);
    }
}

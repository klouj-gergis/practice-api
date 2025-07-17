<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Cache::remember('products', 300, function(){
            return Product::all();
        });
        return response()->json([
            'success' => true,
            'message' => 'products retrieved successfully',
            'data' => $products,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'required|string|max:255|unique:products',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        if($request->hasFile('image')){
            $validatedData['image'] = $request->file('image')->storeAs('products', $validatedData['slug'], 'public');
        }

        $product = Product::create($validatedData);
        Cache::forget('products');
        return response()->json([
            'message' => 'Product added successfully',
            'success' => true,
            'data' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {

        $productCached = Cache::remember('product_' . $product->id, 300, function() use ($product){
            return $product;
        });
        return response()->json([
            'message' => 'product retrieved successfully',
            'success' => true,
            'data' => $product
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:products',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'sku' => 'sometimes|required|string|max:255|unique:products',
        'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        if($request->hasFile('image')){
            $validatedData['image'] = $request->file('image')->storeAs('products', $product->slug, 'public');
        }

        if($request->has('name')){
            $product->name = $request->name;
            $product->slug = Str::slug($request->name, '-');
        };

        if($request->has('description')) $product->description = $request->description;
        if($request->has('price')) $product->price = $request->price;
        if($request->has('stock')) $product->stock = $request->stock;
        if($request->has('sku')) $product->sku = $request->sku;

        Product::save();
        Cache::forget('product_' . $product->id);
        return response()->json([
            'message' => 'products updated successfully',
            'success' => true,
            'data' => $product
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Delete vs Soft delete
        if($product->image){
            Storage::disk('public')->delete($product->image);
        }


        Cache::forget('product_' . $product->id);
        Cache::forget('products');

        Product::delete($product);

        return response()->json([
            'message' => 'product deleted successfully',
            'success' => true,
        ], 200);
    }

    public function undoDelete(Request $request, Product $product){
        if(!$request->user()->hasRole('admin')){
            return response()->json([
                'message' => 'you are not authorized to perform this action',
                'success' => false,
            ], 403);
        };

        $product->restore();

        return response()->json([
                'message' => 'product restored successfully',
                'success' => true,
            ], 200);
    }

    public function permanentDelete(Request $request, Product $product){
        if(!$request->user()->hasRole('admin')){
            return response()->json([
                'message' => 'you are not authorized to perform this action',
                'success' => false,
            ], 403);
        }

        $product->forceDelete();
        return response()->json([
                'message' => 'product restored successfully',
                'success' => true,
            ], 200);
    }

    public function adminIndex(Request $request){
        if($request->user()->hasRole('admin')){
            $products = Product::withTrashed()->get();
            return response()->json([
                'message' => 'products retrieved successfully',
                'success' => true,
                'data' => $products
            ], 200);
        }

        return response()->json([
            'message' => 'you are not authorized to perform this action',
            'success' => false
        ], 403);
    }

    public function filter(Request $request){
        $products = Product::query()
        ->when($request->price_min, fn($query) => $query->where('price', '>=', $request->price_min))
        ->when($request->price_min, fn($query) => $query->where('price', '<=', $request->price_max))
        ->when($request->q, function($query) use ($request) {
            $query->where(fn($query) =>
                $query->where('name', 'like', "%{$request->q}%")
                ->orWhere('description', 'like', "%{$request->q}%")
            );
        })->get();

        return response()->json([
            'message' => 'products retrieved successfully',
            'success' => true,
            'data' => $products
        ], 200);
    }
}

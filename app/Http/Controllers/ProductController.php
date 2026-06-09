<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Carbon\Carbon;

class ProductController extends Controller
{
    //creating new product
    public function store(Request $request){
        $request->validate([
            'category_id' => 'required|numeric|exists:categories,id',
            'name'   => 'required|string|unique:products,name',
            'barcode'  => 'nullable|string',
            'selling_price' => 'nullable|numeric',
            'buying_price'   => 'nullable|numeric',
            'stock_quantity'  => 'nullable|integer',
            'status'       => 'nullable|in:active,inactive'
        ],
        [
            'name.unique'   => 'product with the same name already exists please try again',
            'category_id.exists' => 'no category matching to your ID',
            'status.in'     => 'product status must be active or inactive'
        ]);


        $product = Product::create([
                    'category_id'  => $request->category_id,
                    'name'     => $request->name,
                    'barcode'  => $request->barcode?? null,
                    'buying_price'  => $request->buying_price ?? 0,
                    'selling_price'  => $request->selling_price ?? 0,
                    'stock_quanttiy'  => $request->stock_quantity ?? 0,
                    'status'    => $request->status ?? 'active'
        ]);


        return response()->json([
            'status'  => 'success',
            'message' => 'product created successfully!',
            'data'    => $product
        ], 201);




    }

    //Updating product details

    public function update(Request $request, $id){
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'status' => false,
                'message'  => 'NO product found'
            ], 404);
        }


        $fields = $request->validate([
            'category_id'  => 'sometimes|numeric|exists:categories,id',
            'name'     => 'sometimes|string',
            'barcode'   => 'sometiems|string',
            'buying_price' => 'sometimes|numeric',
            'stock_quantity'  => 'sometimes|integer',
            'status'    => 'sometimes|in:active,inactive'
        ]);



        $product->update($fields);


        return response()->json([
            'status' => 'success',
            'message' => 'product updated successfuly!',
            'updated fields'  => array_keys($fields)
        ], 200);
    }

    //Deleting product
    public function destroy($id){
        $product = Product::find($id);

        if(!$product){
            return response()->json([
                'status'  => false,
                'message' => 'No product found matching to your choice'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'status'  => true,
            'message' => 'product deleted successfully!'
        ], 200);

    }

    //Fetching products 
    public function index(Request $request){
        $user = $request->user();
        $request->validate([
            'category_id' => ['nullable', 'numeric'],
            'category_name' => ['nullable', 'string'],
            'product_id'   => ['nullable', 'numeric'],
            'product_name'     => ['nullable', 'string'],
            'product_status'   => ['nullable', 'string', 'in:active,inactive']
            
        ]);

        $query = Product::with(['category']);


        //serching by category
        if($request->filled('category_id')){
            $query->whereHas('category', function($q) use($request){
                $q->where('id', $request->category_id);
                
            });
        }
        if($request->filled('category_name')){
            $query->whereHas('category', function($q) use($request){
                $q->where('name', 'like', '%'. $request->category_name . '%');
                
            });
        }
        //searching by product
        if($request->filled('product_id')){
            $query->where('id', $request->product_id);
        }
        if($request->filled('product_name')){
            $query->where('name', 'like', '%' . $request->product_name. '%');
        }
        if($request->filled('product_status')){
            $query->where('status', $request->product_status);
        }


        $products = $query->paginate(10)->map(function ($product) use($user){
                    return [
                        'Product number'  => $product->id,
                        'Product name'    => $product->name,
                        'Product Category' => $product->category?->name,
                        'Product buying price'    => $product->buying_price,
                        'Product seling price'   => $product->selling_price,
                        'Product balance'    => $product->stock_quantity,
                        'Product status'    => $product->status,
                        'Product created at'  => Carbon::parse($product->created_at)->format('Y-m-d H:i:s'),
                        // 'Created By'     => $user->name,
                        // 'Creator Role'   => $user->role
                    ];
        });


        if($products->isEmpty()){
            return response()->json([
                'status'  => false,
                'message'  => 'No Product Record found'
            ], 200);
        }


        return response()->json([
            'status'  => true,
            'count'   => $products->count(),
            'data'   => $products
        ], 200);
    }
}


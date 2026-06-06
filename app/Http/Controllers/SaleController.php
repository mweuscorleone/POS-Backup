<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;


class SaleController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'customer_id' => 'required|numeric|exists:customers,id',
            'payment_method' => 'required|string|in:cash,credit,mobile,card',
            'items'    => 'required|array|min:1',
            'items.*.product_id' => 'required|numeric|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:1'

        ]);

        try{
            DB::beginTransaction();

            //CHECK BALANCE FOR SELECTED PRODUCTS 
            foreach($request->items as $item){
                $product = Product::find($item['product_id']);

                $stock = Stock::where('product_id', $product->id)->first();

                if(!$stock || $stock->quantity < $item['quantity']){

                    DB::rollBack();
                    return response()->json([
                        'status'  => false,
                        'message' => 'Insufficient Item balance'
                    ], 400);
                }
            }
            //CALCULATE TOTAL
            $total_amount = 0;

            foreach($request->items as $item){
                $product = Product::find($item['product_id']);

                $subtotal = $item['quantity'] * $product->selling_price;

                $total_amount += $subtotal;
            }

            //CREATE SALE

            $sale = Sale::create([
                'sale_no' => 'SAL'. now()->format('YmdHisu').rand(100, 9999),
                'customer_id' => $request->customer_id,
                'payment_method' => $request->payment_method,
                'total_amount'   => $total_amount,
                'payment_status' => 'completed',
                'created_by'    => $request->user()->id
            ]);
        

            //CREATE SALE ITEM

            foreach($request->items as $item){
                $product = Product::find($item['product_id']);

                $saleItem = SaleItem::create([
                    'sale_id'  => $sale->id,
                    'product_id'  => $product->id,
                    'selling_price'  => $product->selling_price,
                    'quantity'   => $item['quantity'],
                    'subtotal'   => $product->selling_price * $item['quantity']

                ]);

                //REDUCE STOCK
                $stock = Stock::where('product_id',$item['product_id'])->first();

                $stock->decrement('quantity', $item['quantity']);
            
            }
            //RETURNING SALES DATA
            $soldData = Sale::with(['customer','item', 'item.product'])->where('id', $sale->id)->first();

            $data = [];
            $products = [];
            foreach($soldData->item as $item){

               $products[] = [
                                'product name'  => $item->product->name,
                                'selling price'  => $item->product->selling_price,
                                'previous balance' => $item->product->quantity,
                                'quanttiy'     => $item->quantity,
                                'subtotal'    => $item->subtotal
                            ];
            }
            $totalProducts = count($products);
            $customerDetails = [
                            'customer name'  => $soldData->customer->name,
                            'customer address'    => $soldData->customer->address,
                            'customer email'   => $soldData->customer->email,
                            'customer contact' => $soldData->customer->phone  

            ];
            $user = User::find($soldData->created_by);

            $data[] = [
                'Sale ID'   => $soldData->id,
                'Sale NO'   => $soldData->sale_no,
                'Payment Method' => $soldData->payment_method,
                'Payment status'  => $soldData->payment_status,
                'Customer details' => $customerDetails,
                'Product details'   => $products,
                'Total amount'     => $soldData->total_amount,
                'Total products'    => $totalProducts,
                'Created by'      => $user->name,
                'Creator Role'   => $user->role,
                'Created Datetime'  => Carbon::parse($soldData->created_at)->format('Y-m-d H:i:s')

            ];


            DB::commit();

            return response()->json([
                'status'  => true,
                'message'  => 'sales made successfully!',
                'data'     => $data
            ], 200);


            

        }

        catch(Exception $e){
            DB::rollBack();

            Log::error('sales-error' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'something went wrong',
                'error'   => $e->getMessage(),
                'line'   => $e->getLine(),
                'file'   => $e->getFile()

            ], 500);
        }
    }
}

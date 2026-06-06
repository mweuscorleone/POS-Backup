<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;
use App\Models\Stock;
use App\Models\User;

class PurchaseController extends Controller
{
    //create purchase request
    public function store(Request $request){
        $request->validate([
            'supplier_id' => 'required|numeric|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'items'     => 'required|array|min:1',
            'items.*.product_id' => 'required|numeric|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:1',
            'items.*.buying_price'  => 'required|numeric',
            'status'   => 'sometimes|in:pending,received,cancelled'
        ]);

        try{
            DB::beginTransaction();

            $total_amout = 0;
            //calculate total amount
            foreach($request->items as $item){
                $total_amout += $item['buying_price'] * $item['quantity'];
            }
                $purchase_no = 'PUR' . now()->format('YmdHisu');

                //create purchase

                $purchase = Purchase::create([
                            'purchase_no' => $purchase_no,
                            'purchase_date'=>$request->purchase_date,
                            'supplier_id'  => $request->supplier_id,
                            'total_amount'  => $total_amout,
                            'created_by'   => $request->user()->id,                           
                ]);
            

            //create purchase items

            foreach($request->items as $item){
                $subtotal = $item['buying_price'] * $item['quantity'];

                $purchaseItem = PurchaseItem::create([
                            'purchase_id' => $purchase->id,
                            'product_id'  => $item['product_id'],
                            'buying_price'  => $item['buying_price'],
                            'quantity'      => $item['quantity'],
                            'subtotal'    => $subtotal
                ]);

                //updating stock

                $stock  = Stock::firstOrCreate(
                            ['product_id' => $item['product_id']],
                            ['quantity'  => 0]
                );

                $stock->increment('quantity', $item['quantity']);

               
            }
            $purchase->update([
                    'status'   => 'received'
                ]);
            $data  = [];
            $purchaseData = Purchase::with(['supplier', 'item', 'item.product'])->where('id', $purchase->id)
                    ->first();
                    $user = User::where('id', $purchaseData->created_by)->first();

                    
                    foreach($purchaseData->item as $purchasedItem){
                       $products[] =   [ 'Product name' => $purchasedItem->product->name,
                                        'Last buying price' => $purchasedItem->product->buying_price,
                                        'Buying price'  => $purchasedItem->buying_price,
                                        'Prevous quantity'  => $purchasedItem->product->quantity,
                                        'Quantity'      => $purchasedItem->quantity,
                                        'subtotal'      => $purchasedItem->subtotal
                    ];

                    }
                    
                    $total_products = count($products);
                    $createdAt = Carbon::parse($purchaseData->created_at)->format('Y-m-d H:i:s');
            $data[] = [
                        'Purchase ID' => $purchaseData->id,
                        'Purchase No'  => $purchaseData->purchase_no,
                        'Purchase date'  => $purchaseData->purchase_date,
                        'Supplier'    => $purchaseData->supplier->name,
                        'Supplier phone' => $purchaseData->supplier?->phone,
                        'Supplier email'  => $purchaseData->supplier?->email,
                        'Supplier address'  => $purchaseData->supplier?->address,
                        'Products'     =>  $products,
                        'Total Products' => $total_products,
                        'Total amount'  => $purchaseData->total_amount,
                        'created by'    => $user->name,
                        'creator role'   => $user->role,
                        'created at'     => $createdAt

                    ];

            DB::commit();

            return response()->json([
                'status'  => true,
                'message'  => 'Purchases created successfully!',
                'data'     => $data
            ], 200);
            
            
        }

        catch (Exception $e){

            DB::rollBack();

            Log::error('purchase-error' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }


    }
}

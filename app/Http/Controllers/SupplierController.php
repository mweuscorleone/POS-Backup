<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use Carbon\Carbon;

class SupplierController extends Controller
{
    //ADD NEW SUPPLIER
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|unique:suppliers,name',
            'phone' => ['required', 'regex:/^(07|06)[0-9]{8}$/', 
                        'unique:suppliers,phone'],
            'email' => 'nullable|email',
            'address' => 'nullable|string'
        ],
        [
            'name.unique' => 'supplier with the same name already exists',
            'phone.unique' => 'supplier with the same phone number already exists'

        ]);

        $supplier = Supplier::create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'email' => $request->email ?? null,
                    'address' => $request->address ?? null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier added successfully!',
            'data'    => $supplier
        ], 201);
    }

    public function update(Request $request, $id){
        $supplier = Supplier::find($id);

        if(!$supplier){
            return response()->json([
                'status' => 'failed',
                'message' => 'no supplier information found matching to your selection'
            ], 404);


        }
        $fields = $request->validate([
            'name'  => 'sometimes|string',
            'phone'  => [
                            'sometimes',
                            'string',
                            'regex:/^(07|06)[0-9]{8}$'
            ],
            'email' => 'sometimes|email',
            'address' => 'sometimes|string'
        ]);

        $supplier->update($fields);

        return response()->json([
            'status'  => 'success',
            'message' => 'updated successfully!',
            'updted fields' => array_keys($fields)
        ], 200);
    }

    public function destroy($id){
        $supplier = Supplier::find($id);
        if(!$supplier){
            return response()->json([
                'message' => 'No supplier found matching to your choice'
            ], 404);
        }

        $supplier->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'supplier deleted successfully!'
        ], 200);
    }

    public function index(Request $request){
        $request->validate([
            'name' => ['nullable', 'string'],
            'phone' => ['nullable', 'string']
        ]);

        $query = Supplier::query();

        if($request->filled('name')){
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if($request->filled('phone')){
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        $suppliers = $query->orderBy('created_at', 'desc')->get()->map(function ($detail) use ($request){
                    return [
                        'Supplier ID' => $detail->id,
                        'Supplier name' => $detail->name,
                        'Supplier phone' => $detail->phone,
                        'Supplier email' => $detail->email,
                        'Created by'   => $request->user()->name,
                        'Created role'  => $request->user()->role,
                        'Created datetime' => Carbon::parse($detail->created_at)->format('Y-m-d H:i:s')
                    ];
                    });
        if($suppliers->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'No supplier data found'
            ], 200);
        }
        
        return response()->json([
            'status' => true,
            'count'  => $suppliers->count(),
            'data'    => $suppliers
        ], 200);
        
    
    }
}

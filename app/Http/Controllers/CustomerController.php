<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Carbon\Carbon;

class CustomerController extends Controller
{
    //Create new customer
    public function store(Request $request){
      
        $request->validate([
            'name'  => 'required|string',
            'email'  => 'required|email|unique:customers,email',
            'phone'  => ['required',
                        'string',
                        'regex:/^(07|06)[0-9]{8}$/'],
            'address'  => 'sometimes|string'
        ], 
        [
            'email.unique'  => 'customer with the same email already exist',
            'phone.regex'  => 'phone number must start with 07 or 06 and contains 10 digits'
        ]);


        $customer = Customer::create([
                    'name'   => $request->name,
                    'email'  => $request->email,
                    'phone'  => $request->phone,
                    'address'  => $request->address ?? null
        ]);


        return response()->json([
            'status'   => 'success',
            'message'  => 'customer created successfully!',
            'data'     => $customer
        ], 201);
    }

    //updating customer details
    public function update(Request $request, $id){
        $customer = Customer::find($id);
        if(!$customer){
            return response()->json([
                'status'  => 'fail',
                'message' => "customer with an ID {$id} do not exist"
            ], 404);
        }

        $fields = $request->validate([
            'name'   => 'sometimes|string',
            'email'   => 'sometimes|email|unique:customers,email',
            'phone'   => ['sometimes',
                            'string',
                            'regex:/^(07|06)[0-9]{8}$/'],
            'address'  => 'sometimes|string'
        ]);


        $customer->update($fields);

        return response()->json([
            'status'  => 'success',
            'message'  => 'customer updated successfully!',
            'updated fields'  => array_keys($fields)
        ], 200);
    }

    //deleting customer
    public function destroy($id){
        $customer = Customer::find($id);
        if(!$customer){
            return response()->json([
                'status'  => 'fail',
                'message'  => "customer with an ID {$id} is not found"
            ], 404);
        }

        $customer->delete();

        return response()->json([
            'status'   => 'success',
            'message'  => 'customer deleted successfully!'
        ], 200);
    }


    //fetching customers by filtering

    public function index(Request $request){
        $request->validate([
            'name'   => ['nullable', 'string'],
            'phone'  => ['nullable', 'numeric', 'regex:/^(07|06)[0-9]{8}$/'],
            'email'  => ['nullable', 'string']
        ],
        [
            'phone.regex' => 'phone number must be 10 digits and start with 07 0r 06'
        ]);

        $query = Customer::query();

        if($request->filled('name')){
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if($request->filled('phone')){
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        if($request->filled('email')){
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        $customers = $query->get()->map(function ($detail) use ($request){
                    return [
                        'customer no'  => $detail->id,
                        'customer name'  => $detail->name,
                        'customer email'  => $detail->email,
                        'customer phone'   => $detail->phone,
                        'customer address'  => $detail->address,
                        'created at'      => Carbon::parse($detail->created_at)->format('Y-m-d H:i:s'),
                        'created by'   => $request->user()->name,
                        'creator role'  => $request->user()->role
                    ];
        });


        if($customers->isEmpty()){
            return response()->json([
                'status'  => false,
                'message'  => 'No customer data found'
            ], 200);
        }


        return response()->json([
            'status'   => true,
            'count'   => $customers->count(),
            'data'    => $customers
        ], 200);
    }
}

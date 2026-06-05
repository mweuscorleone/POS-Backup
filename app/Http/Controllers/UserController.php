<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    //creating / add new user to the system
    
    public function store(Request $request){
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:4',
            'role'   => 'required|in:admin,manager,cashier'
        ],
        [
            'email.unique' => 'user with the same email account already exists please try again',
            'role.in'   => 'role must be admin, cashier or manager' 
        ]);

        $user = User::create([
                'name'  => $request->name,
                'email'  => $request->email,
                'password' => bcrypt($request->password)
        ]);

        return response()->json([
            'message' => 'user created successfully!',
            'data'   => $user
        ], 201);
    }
    //Updating existing user information
    public function update(Request $request, $id){
        $user = User::find($id);
        if(!$user){
            return response()->json([
                'message'  => "user with an ID {$id} is not found please try again"
            ], 404);
        }

        $fields = $request->validate([
            'name'  => 'sometimes|string',
            'email'  => 'sometimes|email|unique:user,email',
            'password' => 'sometimes|string|min:4'
        ]);

       if(isset($fields['password'])){
        $fields['password'] = bcrypt($fields['password']);
       }

       $user->update($fields);


       return response()->json([
            'message' => 'user updated successfully!'
       ], 200);
    }
    //deleting User account
    public function destroy($id){
        $user = User::find($id);
        if(!$user){
            return response()->json([
                'message'  => 'User not found'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'message'  => 'user deleted successfully'
        ], 200);
    }

    //Get all user account created

    public function index(Request $request){

        $request->validate([
            'name'  => ['nullable', 'string'],
            'email'  => ['nullable', 'email'],
            'role'  => ['nullable', 'string', 'in:admin,cashier,manager']
        ]);


        $query = User::query();

        if($reqeust->filled('name')){
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if($reqeust->filled('email')){
            $query->where('email',  $request->email);
        }
        if($reqeust->filled('role')){
            $query->where('role', $request->role);
        }

        $users = $query->get();

        if($users->isEmpty()){
            return response()->json([
                'message'  => 'No user data found'
            ], 200);
        }

        return response()->json([
            'status'  => true,
            'count'  => $users->count(),
            'data'  => $users
        ], 200);



    }
}

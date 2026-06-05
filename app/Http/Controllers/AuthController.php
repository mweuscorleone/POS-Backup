<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{   
    //USER LOGIN
    public function login(Request $request){
        $request->validate([
            'email'  => 'required|email|exists:users,email',
            'password' => 'required|string|min:4'
        ]);


        if(!$token = Auth::guard('api')->attempt($request->only('email', 'password'))){
            return response()->json([
                'message'  => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'message'  => 'login successfully!',
            'token'   => $token,
            'token expire in' => Carbon::now()->addMinutes((auth('api')->factory()->getTTL()))->diffForHumans()
        ], 200);
    }
    //USER LOGOUT
    public function logout(){

        auth('api')->logout();

        return response()->json([
            'message'  => 'logout successfully!'
        ], 200);
    }
    //PASSWORD RESET
    //REQUEST PASSWORD RESET TOKEN
    public function store(Request $request){
        $request->validate([
            'email'  => 'required|email|exists:users,email',
            
        ],
        [
            'email.exists' => 'email do not exists please try again'
        ]);

        $user = User::where('email', $request->email)->first();
        $plainToken = Str::random(100);
        $hashedToken = hash('sha256', $plainToken);


        DB::table('password_reset_tokens')->updateOrInsert(
                        ['email' => $user->email],
                        ['token' => $hashedToken,
                        'created_at' => now()
                        ]);


        $data = DB::table('password_reset_tokens')->where('email', $user->email)->first();
                        


        return response()->json([
            'status'  => 'success',
            'message' => 'Token generated successfully!',
            'token'   => $plainToken,
            'token expired in' => Carbon::parse($data->created_at)->addMinutes(6)->diffForHumans(),
            'use token before'  => Carbon::parse($data->created_at)->addMinutes(6)->format('Y-m-d H:i:s')
        ], 200);
    }

    //PASSWORD RESET BY TOKEN
    public function resetPassword(Request $request){
        $request->validate([
            'email'  => 'required|email|exists:users,email',
            'password' => 'required|string|min:4',
            'token'   => 'required|string'
        ]);


        try{

            DB::beginTransaction();

            $user = DB::table('password_reset_tokens')->where('email', $request->email)->first();

            if(!$user){
            return response()->json([
                        'message' => 'provided email did\'t have reset password token please generate token first'
                          ], 400);
                    }

       

            $expired = Carbon::parse($user->created_at)->addMinutes(6)->isPast();

            if($expired){
            return response()->json([
                'message' => 'Provided token already expired, please try again'
            ], 400);
            }
            $hashedToken = hash('sha256', $request->token);

          if(!hash_equals($user->token, $hashedToken)){
            return response()->json([
                'message' => 'Invalid Token try again'
            ], 400);
             }

          User::where('email', $user->email)->update([
            'password'  => bcrypt($request->password)
          ]);

          DB::table('password_reset_tokens')->where('email', $user->email)->delete();

          DB::commit();

          return response()->json([
            'message'  => 'Password changed successfullly'
          ], 200);



        }

        catch (Exception $e){
            DB::rollBack();

            Log::error('reset-password-err'. $e->getMessage());

            return response()->json([
                'message' => 'something went wrong',
                'error'  => $e->getMessage()
            ], 500);
        }
            
    }
    
}

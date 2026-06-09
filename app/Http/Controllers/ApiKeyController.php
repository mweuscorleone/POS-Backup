<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiKey;
use Illuminate\Support\Str;
class ApiKeyController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $plainKey = Str::random(200);
        $hashedKey = hash('sha256', $plainKey);


       $data = ApiKey::updateOrCreate(
                ['name' => $request->name],
                ['api_key'  => $hashedKey,
                'is_active'  => $request->is_active?? true]
        );

        return response()->json([
            'message'  => 'Api key generated successfully!',
            'Api key'  => $plainKey,
            'data'    => $data
        ], 201);
    }
}

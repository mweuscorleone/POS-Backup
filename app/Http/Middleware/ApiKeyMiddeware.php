<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKey;

class ApiKeyMiddeware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   
        $apiKey = $request->header('X-API-KEY');

        if(!$apiKey){
            return response()->json([
                'status' => false,
                'message' => 'Api key is required'
            ], 401);

        }
        $hashedKey = hash('sha256', $apiKey);

        $key = ApiKey::where('api_key', $hashedKey)->where('is_active', true)->first();

        if(!$key){
            return response()->json([
                'status' => false,
                'message' => 'Invalid Api key'
            ], 401);
        }
        return $next($request);
    }
}

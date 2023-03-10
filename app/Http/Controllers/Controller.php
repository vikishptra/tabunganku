<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Models\User;



class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function messagesSuccess(mixed $data, string $messages, int $statusCode): JsonResponse
    {
        return response()->json([
            'success' => true,
            'messages' => $messages,
            'data' => $data,
        ], $statusCode);
    }

    public function messagesError(mixed $messages, int $statusCode):JsonResponse
    {
        return response()->json([
            'success' => false,
            'messages' => $messages,
            'data' => null,
        ], $statusCode);
    }


    protected function createNewToken($token){
        $user = auth()->user();
        $user->update(['refresh_token' => $token]);
        $responseData = [
            'success' => true,
            'message' => 'ok success',
            'data' => $user,
        ];
        if (!empty($token)) {
            $responseData['access_token'] = $token;
        }

        return response()->json($responseData, 200);
    }
    
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

}
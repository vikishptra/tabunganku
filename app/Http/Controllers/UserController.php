<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string',
            'confirm_password'=> 'required|string|same:password',
        ]);
        if ($validator->fails()) {
                $errors = array_values(array_filter($validator->errors()->all()));
                return $this->messagesError($errors, 400);
        }
        
        $verificationCode = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6) . mt_rand(100000, 999999);
        $user = User::create(array_merge(
            $validator->validated(),
            [
                'password' => bcrypt($request->password),
                'status'=> false,
                'money'=>0,
                'verification_code'=>$verificationCode
            ]
                ));
        return $this->messagesSuccess($user,"ok success", 201);

    }

    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            $errors = array_values(array_filter($validator->errors()->all()));
            return $this->messagesError( $errors, 400);
        }
        if (! $token = auth()->attempt($validator->validated())) {
            return $this->messagesError('Email atau password anda salah !',400);
        }

        return $this->createNewToken($token);
    }

    public function userProfile()
    {
        $user = auth()->user();
        if ($user) {
            return $this->messagesSuccess($user,"ok success", 200);
        } else {
            return $this->messagesError('User Not Found',401);
        }
    }
    
}




<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Models\DetailSaldoUser;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(RegisterUserRequest $request) {

        try {
            $id_ewallet = "ewallet-".Str::random(4);
            $verificationCode = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4) . mt_rand(1000, 9999);
            $user = User::create(array_merge(
                $request->validated(),
                [
                    'password' => bcrypt($request->password),
                    'status'=> false,
                    'money'=>0,
                    'verification_code'=>$verificationCode
                ]
                    ));

            $saldo_user = DetailSaldoUser::create(array_merge(
                [
                    'id' => $id_ewallet,
                    'id_user' => $user->id,
                    'saldo' => 0
                ]
                ));
                
            return $this->messagesSuccess($user,"ok success", 201);

        } catch (\Exception $e) {
            return $this->messagesError('Terjadi Kesalahan'.$e->getMessage(),400);
        }   

    }

    public function login(LoginUserRequest $request){

        try {
            if (! $token = auth()->attempt($request->validated())) {
                return $this->messagesError('Email atau password anda salah !',400);
            }
            $user = auth()->user();
            if (!$user->status) {
                return $this->messagesError('Akun belom aktif periksa email anda!',400);
            }
            
            return $this->createNewToken($token);

        } catch (\Exception $e) {
             return $this->messagesError('Terjadi Kesalahan'.$e->getMessage(),400);
        }

    	
    }
    
    public function userProfile(Request $request)
    {
        try {
            $user = auth()->user();
            if ($user) {
                
                return $this->messagesSuccess($user,"ok success", 200);
            } else {
                return $this->messagesError('User Not Found',401);
            }
        } catch (\Exception $e) {
            return $this->messagesError('Terjadi Kesalahan'.$e->getMessage(),400);

        }
    }
    
}




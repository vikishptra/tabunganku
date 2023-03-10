<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Xendit\Xendit;
use Validator;
use Illuminate\Support\Str;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Http;


class TransaksiVAController extends Controller
{
    public function chargeVA(Request  $request){
        try {
            $secret_key = 'Basic ' . config('xendit.key_auth');
            $external_id = Str::random(10);
            $validator = Validator::make($request->all(), [
                'amount' => 'required',
                'bank_code' =>'required',
            ]);
            if ($validator->fails()) {
                    $errors = array_values(array_filter($validator->errors()->all()));
                    return $this->messagesError($errors, 400);
            }
            $user= auth()->user();
            $data_request = Http::withHeaders([
                'Authorization' => $secret_key
            ])->post('https://api.xendit.co/callback_virtual_accounts', [
                'external_id' => 'va-'.$external_id,
                'expected_amount' => $request->amount,
                'name' => $user->name,
                'with-fee-rule' => 4200,
                'bank_code' => $request->bank_code
            ]);
            $response = $data_request->json();
            if (isset($response['status'])) {
                $transaksi = Transaksi::create(array_merge(
                    $validator->validated(),
                    [
                        'id' => 'va-'.$external_id,
                        'id_user' => $user->id,
                        'status' => $response['status']
                    ]
                ));
                return $this->messagesSuccess($response,"ok success", 201);
            } else {
                return $this->messagesError('Terjadi Kesalahan '.$response['errors'][0]['messages'][0],400);
            }
        } catch (\Exception $e) {
            return $this->messagesError('Terjadi Kesalahan '.$e->getMessage(),400);

        }
        
       
      

    }   
}
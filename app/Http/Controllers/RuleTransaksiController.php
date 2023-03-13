<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RuleTransaksiBank;
use App\Http\Requests\RuleTransaksiBankRequest;


class RuleTransaksiController extends Controller{
    
    public function createRuleBank(RuleTransaksiBankRequest $request){
        $ruleBankTransaksi = RuleTransaksiBank::where('bank_code', $request->bank_code)->first();
        if ($request->bank_code == $ruleBankTransaksi->bank_code) {
            $ruleBankTransaksi->bank_code = $request->bank_code;
            $ruleBankTransaksi->rule_transaksi = $request->rule_transaksi;
            $ruleBankTransaksi->save();
            return $this->messagesSuccess($ruleBankTransaksi,"ok success", 200);

        }
        $rule = RuleTransaksiBank::create(array_merge(
            $request->validated(),
            [
                'bank_code' => $request->bank_code,
                'rule_transaksi'=>$request->rule_transaksi
            ]
            ));
        return $this->messagesSuccess($rule,"ok success", 201);
    }
  

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RuleTransaksiBank;
use App\Http\Requests\RuleTransaksiBankRequest;



class RuleTransaksiController extends Controller{
    
    public function createRuleBank(RuleTransaksiBankRequest $request){
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

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;


class SimulasiTabunganRencanaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'target_tabungan' => 'required|numeric|min:1',
            'jumlah_uang_saat_ini' => 'required|numeric|min:0',
            'nabung' => [
                'required',
                'array',
                'size:1',
            ],
        ];
    }
    


    protected function failedValidation(Validator $validator)
    {
        $errors = array_values(array_filter($validator->errors()->all()));
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => $errors,
            'data' => null
        ], 400));
    }
}

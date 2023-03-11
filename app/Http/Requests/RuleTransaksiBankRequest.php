<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RuleTransaksiBankRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'bank_code' => 'required',
            'rule_transaksi' => 'required',
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

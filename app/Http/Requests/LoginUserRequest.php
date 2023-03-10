<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
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

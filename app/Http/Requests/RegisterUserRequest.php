<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string',
            'confirm_password'=> 'required|string|same:password',
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

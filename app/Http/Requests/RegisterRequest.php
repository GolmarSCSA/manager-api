<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'terms_conditions' => 'required|accepted',
            'privacy_policy' => 'required|accepted',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        return response()->json([
            'success' => false,
            'errors' => $errors
        ], 422);
    }
}
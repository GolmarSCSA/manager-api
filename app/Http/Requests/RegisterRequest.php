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
            'company' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'prefix_id' => 'nullable|integer|exists:countries,id',
            'role_id' => 'required|integer|exists:roles,id',
            'terms_conditions' => 'required|accepted',
            'privacy_policy' => 'required|accepted',
            'country_id' => 'nullable|integer|exists:countries,id',
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
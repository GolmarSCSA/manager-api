<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FirstStepRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'company' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'prefix_id' => 'nullable|integer|exists:countries,id',
            'role_id' => 'required|integer|exists:roles,id',
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
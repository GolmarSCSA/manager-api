<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use App\Models\Country;
class UserValidationController extends Controller
{
        /**
     * Validate the user input.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateUserFirstStep(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Validation passed.'
        ]);
    }

    public function firstStepData(Request $request){
        $lang = $request->input('lang', 'es'); 
        $roles = Role::whereIn('id', [config('app.roles.installer'), config('app.roles.building_administrator')])->get();
        $countries = Country::all();

        $result_countries = $countries->map(function ($country) use ($lang) {
            return [
                'id' => $country->id,
                'name' => (__('countries.' . $country->language_field, [], $lang) ?? $country->country_es),
                'code' => $country->codeISO2,
                'prefix' => $country->tel_prefix,
            ];
        });

        $result_roles = $roles->map(function ($role) use ($lang) {
            return [
                'id' => $role->id,
                'name' => trans('roles.' . $role->name, [], $lang),
            ];
        });

        return response()->json([
            'status' => 'success',
            'roles' => $result_roles,
            'countries' => $result_countries,
        ]);
    }
}

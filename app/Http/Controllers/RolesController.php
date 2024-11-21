<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function getSelectableRoles(Request $request)
    {
        $roles = Role::whereIn('id', [config('app.roles.installer'), config('app.roles.building_administrator')])->get();

        $lang = $request->input('lang', 'es'); 

        $result = $roles->map(function ($role) use ($lang) {
            return [
                'id' => $role->id,
                'name' => trans('roles.' . $role->name, [], $lang),
            ];
        });

        return response()->json($result);
    }
}

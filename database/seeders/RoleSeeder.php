<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Crear permisos
        $permissions = [

        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        $superAdminRole = Role::updateOrCreate([
            'id' => config(('app.roles.super_admin')),
            'name' => 'super_admin'
        ]);

        // Crear roles y asignar permisos
        $distributorRole = Role::updateOrCreate([
            'id' => config(('app.roles.distributor')),
            'name' => 'distributor'
        ]);

        // Crear roles y asignar permisos
        $installerRole = Role::updateOrCreate([
            'id' => config(('app.roles.installer')),
            'name' => 'installer'
        ]);

        // Crear roles y asignar permisos
        $buildingAdministratorRole = Role::updateOrCreate([
            'id' => config(('app.roles.building_administrator')),
            'name' => 'building_administrator'
        ]);


        //$adminRole->givePermissionTo(Permission::all()); // Admin tiene todos los permisos
        //$editorRole->givePermissionTo(['edit posts', 'delete posts']); // Editor tiene permisos específicos
        //$viewerRole->givePermissionTo('view analytics'); // Viewer tiene un permiso específico
    }
}

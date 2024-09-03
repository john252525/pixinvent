<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionsSeeder extends Seeder
{
    protected string $guard_name = 'web';

    private function createPermission($permissionName): void
    {
        Permission::create([
            'guard_name' => $this->guard_name,
            'name' => $permissionName,
        ]);
    }

    private function createRole($roleName): Role
    {
        return Role::create([
            'guard_name' => $this->guard_name,
            'name' => $roleName,
        ]);
    }

    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $this->createPermission(PermissionsEnum::READ);
        $this->createPermission(PermissionsEnum::CREATE);
        $this->createPermission(PermissionsEnum::UPDATE);
        $this->createPermission(PermissionsEnum::DELETE);
        $this->createPermission(PermissionsEnum::MANAGE);

        // create main roles and assign existing permissions
        $superAdminRole = $this->createRole(RolesEnum::ALL);
        $superAdminRole->givePermissionTo(PermissionsEnum::MANAGE);

        $adminRole = $this->createRole(RolesEnum::ADMIN);
        $adminRole->givePermissionTo([PermissionsEnum::READ, PermissionsEnum::CREATE, PermissionsEnum::UPDATE, PermissionsEnum::DELETE]);

        $userRole = $this->createRole(RolesEnum::USER);
        $userRole->givePermissionTo(PermissionsEnum::READ);

        $premiumRole = $this->createRole(RolesEnum::PREMIUM);
        $premiumRole->givePermissionTo(PermissionsEnum::READ);

        // Assign role to super-admin user
        $superAdmin = User::find(1);
        $superAdmin->assignRole(RolesEnum::ALL);

        // Assign role to admin user
        $admin = User::find(4);
        $admin->assignRole(RolesEnum::ADMIN, RolesEnum::USER, RolesEnum::PREMIUM);

        //Assign role to client user
        $client = User::find(2);
        $client->assignRole(RolesEnum::USER);

        // Assign role to premium user
        $operator = User::find(3);
        $operator->assignRole(RolesEnum::PREMIUM, RolesEnum::USER);
    }
}

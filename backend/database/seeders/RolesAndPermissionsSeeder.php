<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Tenant;
use App\Models\RoleModuleBinding;
use Stancl\Tenancy\Facades\Tenancy;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get the current tenant ID (automatically handled by `Tenancy::run` in DatabaseSeeder)
        $currentTenantId = Tenancy::tenant()->id;

        // Create general permissions (these can be finer-grained)
        $permissions = [
            'manage:users',
            'manage:roles',
            'manage:properties',
            'manage:bookings',
            'manage:iot',
            'manage:seo',
            'manage:culture',
            'view:reports',
        ];

        foreach ($permissions as $permissionName) {
            Permission::updateOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web', 'tenant_id' => $currentTenantId],
                []
            );
        }

        // Create Roles
        $adminRole = Role::updateOrCreate(
            ['name' => 'tenant_admin', 'guard_name' => 'web', 'tenant_id' => $currentTenantId],
            []
        );
        $managerRole = Role::updateOrCreate(
            ['name' => 'property_manager', 'guard_name' => 'web', 'tenant_id' => $currentTenantId],
            []
        );
        $guestRole = Role::updateOrCreate(
            ['name' => 'guest_user', 'guard_name' => 'web', 'tenant_id' => $currentTenantId],
            []
        );

        // Assign all permissions to tenant_admin
        $adminRole->givePermissionTo(Permission::where('tenant_id', $currentTenantId)->get());

        // Assign specific permissions to property_manager
        $managerRole->givePermissionTo([
            'manage:properties',
            'manage:bookings',
            'manage:iot',
            'manage:culture',
            'view:reports',
        ]);

        // Assign basic permissions to guest_user (e.g., only view properties)
        $guestRole->givePermissionTo([
            // 'view:properties' // Assuming a 'view:properties' permission exists, or create it.
            // For now, let's assume no specific view permission for guest
        ]);

        // Define Role Module Bindings for tenant_admin
        // This is a more granular way to define what modules a role can interact with
        $modules = [
            'bookings' => ['read' => true, 'write' => true],
            'properties' => ['read' => true, 'write' => true],
            'iot' => ['read' => true, 'control' => true],
            'seo' => ['read' => true, 'write' => true],
            'culture' => ['read' => true, 'write' => true],
            'users' => ['read' => true, 'write' => true], // Admin can manage users
            'roles' => ['read' => true, 'write' => true], // Admin can manage roles
            'reports' => ['read' => true],
        ];

        foreach ($modules as $moduleName => $modulePermissions) {
            RoleModuleBinding::updateOrCreate(
                ['role_id' => $adminRole->id, 'module' => $moduleName, 'tenant_id' => $currentTenantId],
                ['permissions' => $modulePermissions]
            );
        }

        // Define Role Module Bindings for property_manager
        $managerModules = [
            'bookings' => ['read' => true, 'write' => true],
            'properties' => ['read' => true, 'write' => true],
            'iot' => ['read' => true, 'control' => true],
            'culture' => ['read' => true, 'write' => true],
            'reports' => ['read' => true],
        ];

        foreach ($managerModules as $moduleName => $modulePermissions) {
            RoleModuleBinding::updateOrCreate(
                ['role_id' => $managerRole->id, 'module' => $moduleName, 'tenant_id' => $currentTenantId],
                ['permissions' => $modulePermissions]
            );
        }

        // No specific module bindings for guest_user (they just have direct permissions, if any)
    }
}

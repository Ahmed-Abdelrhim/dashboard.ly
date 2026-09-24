<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Predefined application permissions grouped by module.
     *
     * @var array<string, list<string>>
     */
    public const PERMISSIONS_BY_MODULE = [
        'Users' => [
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
        ],
        'Roles' => [
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
        ],
        'Leads' => [
            'view_leads',
            'create_leads',
            'edit_leads',
            'delete_leads',
            'assign_leads',
            'export_leads',
        ],
        'Campaigns' => [
            'view_campaigns',
            'create_campaigns',
            'edit_campaigns',
            'delete_campaigns',
        ],
        'Settings & Logs' => [
            'manage_settings',
            'view_audit_logs',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * Inserts permissions or ignores them if they already exist.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed all permissions using firstOrCreate (insert or ignore if exists)
        foreach (self::PERMISSIONS_BY_MODULE as $module => $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]);
            }
        }

        // Seed default system roles
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $salesAgentRole = Role::firstOrCreate([
            'name' => 'sales_agent',
            'guard_name' => 'web',
        ]);

        // Super Admin gets all permissions
        $allPermissions = Permission::where('guard_name', 'web')->get();
        $superAdminRole->syncPermissions($allPermissions);

        // Admin gets all except role deletion and system settings
        $adminPermissions = Permission::where('guard_name', 'web')
            ->whereNotIn('name', ['delete_roles', 'manage_settings'])
            ->get();
        $adminRole->syncPermissions($adminPermissions);

        // Sales Agent gets lead operations and view campaigns
        $salesAgentPermissions = Permission::where('guard_name', 'web')
            ->whereIn('name', [
                'view_leads',
                'create_leads',
                'edit_leads',
                'view_campaigns',
            ])
            ->get();
        $salesAgentRole->syncPermissions($salesAgentPermissions);
    }
}

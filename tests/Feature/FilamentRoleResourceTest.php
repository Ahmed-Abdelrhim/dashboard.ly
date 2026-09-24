<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\RoleResource;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentRoleResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_permission_seeder_is_idempotent_and_creates_all_permissions(): void
    {
        $initialPermissionCount = Permission::count();
        $initialRoleCount = Role::count();

        $this->assertGreaterThan(0, $initialPermissionCount);
        $this->assertGreaterThanOrEqual(3, $initialRoleCount);

        // Run seeder again - must not throw duplicate errors and count should stay same
        $this->seed(PermissionSeeder::class);

        $this->assertEquals($initialPermissionCount, Permission::count());
        $this->assertEquals($initialRoleCount, Role::count());

        $this->assertDatabaseHas('roles', ['name' => 'super_admin']);
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('roles', ['name' => 'sales_agent']);

        $this->assertDatabaseHas('permissions', ['name' => 'view_leads']);
        $this->assertDatabaseHas('permissions', ['name' => 'create_leads']);
        $this->assertDatabaseHas('permissions', ['name' => 'view_users']);
        $this->assertDatabaseHas('permissions', ['name' => 'view_roles']);
    }

    public function test_unauthenticated_user_cannot_access_roles(): void
    {
        $response = $this->get(RoleResource::getUrl('index'));
        $response->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_authenticated_user_can_view_roles_list(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'type' => UserType::SuperAdmin,
            'is_active' => true,
        ]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        $response = $this->get(RoleResource::getUrl('index'));
        $response->assertOk();

        Livewire::test(ListRoles::class)
            ->assertCanSeeTableRecords(Role::all())
            ->assertSee('Super Admin')
            ->assertSee('Admin')
            ->assertSee('Sales Agent');
    }

    public function test_admin_can_create_new_role_with_permissions(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'type' => UserType::SuperAdmin,
            'is_active' => true,
        ]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        $permissions = Permission::whereIn('name', ['view_leads', 'create_leads'])->pluck('id')->toArray();

        Livewire::test(CreateRole::class)
            ->fillForm([
                'name' => 'branch_manager',
                'guard_name' => 'web',
                'permissions' => $permissions,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('roles', [
            'name' => 'branch_manager',
            'guard_name' => 'web',
        ]);

        $createdRole = Role::findByName('branch_manager', 'web');
        $this->assertTrue($createdRole->hasPermissionTo('view_leads'));
        $this->assertTrue($createdRole->hasPermissionTo('create_leads'));
        $this->assertFalse($createdRole->hasPermissionTo('delete_roles'));
    }

    public function test_admin_can_edit_role_and_sync_permissions(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'type' => UserType::SuperAdmin,
            'is_active' => true,
        ]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        $customRole = Role::create(['name' => 'support_agent', 'guard_name' => 'web']);
        $customRole->givePermissionTo('view_leads');

        $newPermissions = Permission::whereIn('name', ['view_leads', 'edit_leads'])->pluck('id')->toArray();

        Livewire::test(EditRole::class, ['record' => $customRole->getKey()])
            ->fillForm([
                'permissions' => $newPermissions,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(RoleResource::getUrl('index'));

        $customRole->refresh();
        $this->assertTrue($customRole->hasPermissionTo('view_leads'));
        $this->assertTrue($customRole->hasPermissionTo('edit_leads'));
    }

    public function test_super_admin_role_cannot_be_edited(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'type' => UserType::SuperAdmin,
            'is_active' => true,
        ]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        $superAdminRole = Role::findByName('super_admin', 'web');

        $this->assertFalse(RoleResource::canEdit($superAdminRole));

        $response = $this->get(RoleResource::getUrl('edit', ['record' => $superAdminRole]));
        $response->assertForbidden();
    }

    public function test_user_assigned_role_has_role_and_permissions(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Support User',
            'email' => 'support@crm.com',
            'type' => UserType::SalesAgent,
        ]);

        $user->assignRole('sales_agent');

        $this->assertTrue($user->hasRole('sales_agent'));
        $this->assertTrue($user->hasPermissionTo('view_leads'));
        $this->assertTrue($user->hasPermissionTo('create_leads'));
        $this->assertFalse($user->hasPermissionTo('delete_roles'));
    }

    public function test_user_without_view_roles_permission_cannot_access_roles(): void
    {
        /** @var User $unauthorizedUser */
        $unauthorizedUser = User::factory()->create([
            'type' => UserType::SalesAgent,
            'is_active' => true,
        ]);

        $this->actingAs($unauthorizedUser);

        $response = $this->get(RoleResource::getUrl('index'));
        $response->assertForbidden();
    }

    public function test_user_without_create_roles_permission_cannot_see_create_action_or_access_create_page(): void
    {
        $this->seed(PermissionSeeder::class);

        /** @var User $viewerUser */
        $viewerUser = User::factory()->create([
            'type' => UserType::SalesAgent,
            'is_active' => true,
        ]);
        $viewerUser->givePermissionTo('view_roles');

        $this->actingAs($viewerUser);

        // "New role" (create) header action must be hidden in listing
        Livewire::test(ListRoles::class)
            ->assertActionHidden('create');

        // Direct URL to create page must return 403 Forbidden
        $response = $this->get(RoleResource::getUrl('create'));
        $response->assertForbidden();
    }

    public function test_user_with_create_roles_permission_can_see_create_action_and_access_create_page(): void
    {
        $this->seed(PermissionSeeder::class);

        /** @var User $creatorUser */
        $creatorUser = User::factory()->create([
            'type' => UserType::SalesAgent,
            'is_active' => true,
        ]);
        $creatorUser->givePermissionTo(['view_roles', 'create_roles']);

        $this->actingAs($creatorUser);

        // "New role" (create) header action must be visible
        Livewire::test(ListRoles::class)
            ->assertActionVisible('create');

        // Direct URL to create page must be accessible
        $response = $this->get(RoleResource::getUrl('create'));
        $response->assertOk();
    }
}

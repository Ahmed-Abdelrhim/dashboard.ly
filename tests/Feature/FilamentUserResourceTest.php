<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\UserResource;
use App\Mail\NewUserWelcomeMail;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentUserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_user_resource(): void
    {
        $response = $this->get(UserResource::getUrl('index'));
        $response->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_authenticated_user_can_view_users_list(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'type' => UserType::SuperAdmin,
            'is_active' => true,
        ]);

        $users = User::factory()->count(3)->create();

        $this->actingAs($admin);

        $response = $this->get(UserResource::getUrl('index'));
        $response->assertOk();

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords($users);
    }

    public function test_authenticated_user_can_view_user_details(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'type' => UserType::SuperAdmin,
            'is_active' => true,
        ]);

        Storage::disk('public')->put('avatars/john-wick.webp', 'dummy content');

        /** @var User $targetUser */
        $targetUser = User::factory()->create([
            'name' => 'John Wick',
            'email' => 'john.wick@continental.com',
            'type' => UserType::SalesAgent,
            'image' => 'avatars/john-wick.webp',
        ]);

        $this->actingAs($admin);

        $response = $this->get(UserResource::getUrl('view', ['record' => $targetUser]));
        $response->assertOk();

        Livewire::test(ViewUser::class, ['record' => $targetUser->getKey()])
            ->assertSee('John Wick')
            ->assertSee('john.wick@continental.com')
            ->assertSee('avatars/john-wick.webp');
    }

    public function test_user_without_image_displays_default_initials_avatar_in_view_and_table(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'type' => UserType::SuperAdmin,
            'is_active' => true,
        ]);

        /** @var User $targetUser */
        $targetUser = User::factory()->create([
            'name' => 'Jon Snow',
            'email' => 'jon.snow@winterfell.com',
            'type' => UserType::SalesAgent,
            'image' => null,
        ]);

        $this->actingAs($admin);

        $defaultAvatarUrl = filament()->getUserAvatarUrl($targetUser);
        $this->assertStringContainsString('ui-avatars.com', $defaultAvatarUrl);
        $this->assertStringContainsString('name=J+S', $defaultAvatarUrl);
        $this->assertEquals($defaultAvatarUrl, $targetUser->getAvatarUrl());

        Livewire::test(ViewUser::class, ['record' => $targetUser->getKey()])
            ->assertSeeHtml(e($defaultAvatarUrl));

        Livewire::test(ListUsers::class)
            ->assertSeeHtml(e($defaultAvatarUrl));
    }

    public function test_user_edit_page_loads_existing_avatar(): void
    {
        Storage::disk('public')->put('avatars/baba-yaga.webp', 'dummy content');

        /** @var User $admin */
        $admin = User::factory()->create([
            'type' => UserType::SuperAdmin,
            'is_active' => true,
        ]);

        /** @var User $targetUser */
        $targetUser = User::factory()->create([
            'name' => 'Baba Yaga',
            'email' => 'baba@continental.com',
            'type' => UserType::SalesAgent,
            'image' => 'avatars/baba-yaga.webp',
        ]);

        $this->actingAs($admin);

        $response = $this->get(UserResource::getUrl('edit', ['record' => $targetUser]));
        $response->assertOk();

        $livewire = Livewire::test(EditUser::class, ['record' => $targetUser->getKey()]);
        $component = $livewire->instance()->form->getComponent('image');
        $uploadedFiles = $component->getUploadedFiles();

        $this->assertNotEmpty($uploadedFiles);
        $firstFile = reset($uploadedFiles);
        $this->assertEquals('/storage/avatars/baba-yaga.webp', $firstFile['url']);
    }

    public function test_can_create_user_and_dispatches_welcome_email_with_credentials(): void
    {
        Mail::fake();

        /** @var User $admin */
        $admin = User::factory()->create([
            'type' => UserType::SuperAdmin,
            'is_active' => true,
        ]);

        $this->seed(PermissionSeeder::class);
        $salesAgentRole = Role::findByName('sales_agent', 'web');

        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Clark Kent',
                'email' => 'clark@dailyplanet.com',
                'phone' => '+201012345678',
                'type' => UserType::SalesAgent,
                'roles' => [$salesAgentRole->id],
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Clark Kent',
            'email' => 'clark@dailyplanet.com',
            'phone' => '+201012345678',
            'type' => UserType::SalesAgent->value,
            'is_active' => 1,
        ]);

        $createdUser = User::where('email', 'clark@dailyplanet.com')->firstOrFail();
        $this->assertNotEmpty($createdUser->password);
        $this->assertTrue($createdUser->hasRole('sales_agent'));

        Mail::assertQueued(NewUserWelcomeMail::class, function (NewUserWelcomeMail $mail) use ($createdUser) {
            return $mail->hasTo('clark@dailyplanet.com')
                && $mail->user->id === $createdUser->id
                && strlen($mail->plainPassword) === 12
                && ctype_alnum($mail->plainPassword)
                && Hash::check($mail->plainPassword, $createdUser->password);
        });
    }

    public function test_roles_are_required_when_user_type_is_not_super_admin(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'type' => UserType::SuperAdmin,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Peter Parker',
                'email' => 'peter@dailybugle.com',
                'type' => UserType::SalesAgent,
                'roles' => [],
            ])
            ->call('create')
            ->assertHasFormErrors(['roles' => 'required']);
    }

    public function test_roles_are_optional_when_user_type_is_super_admin(): void
    {
        Mail::fake();

        /** @var User $admin */
        $admin = User::factory()->create([
            'type' => UserType::SuperAdmin,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Diana Prince',
                'email' => 'diana@themyscira.com',
                'type' => UserType::SuperAdmin,
                'roles' => [],
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'diana@themyscira.com',
            'type' => UserType::SuperAdmin->value,
        ]);
    }

    public function test_new_user_welcome_mail_template_renders_properly(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Bruce Wayne',
            'email' => 'bruce@wayneenterprises.com',
            'type' => UserType::Admin,
        ]);

        $plainPassword = 'BatMobile2026#Secure';
        $mailable = new NewUserWelcomeMail($user, $plainPassword);

        $mailable->assertSeeInHtml('Bruce Wayne');
        $mailable->assertSeeInHtml('bruce@wayneenterprises.com');
        $mailable->assertSeeInHtml('BatMobile2026#Secure');
        $mailable->assertSeeInHtml(url('/admin'));
        $mailable->assertSeeInHtml('Open Dashboard');
        $mailable->assertSeeInHtml('Admin');
    }

    public function test_super_admin_can_delete_users_from_listing_table(): void
    {
        $this->seed(PermissionSeeder::class);

        /** @var User $superAdmin */
        $superAdmin = User::factory()->create([
            'type' => UserType::SuperAdmin,
            'is_active' => true,
        ]);

        /** @var User $targetUser */
        $targetUser = User::factory()->create([
            'name' => 'Target To Delete',
            'email' => 'target@delete.com',
            'type' => UserType::SalesAgent,
        ]);

        $this->actingAs($superAdmin);

        Livewire::test(ListUsers::class)
            ->assertTableActionVisible('delete', $targetUser)
            ->callTableAction('delete', $targetUser)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_user_with_delete_users_permission_can_delete_users(): void
    {
        $this->seed(PermissionSeeder::class);

        /** @var User $adminUser */
        $adminUser = User::factory()->create([
            'type' => UserType::Admin,
            'is_active' => true,
        ]);
        $adminUser->givePermissionTo(['view_users', 'delete_users']);

        /** @var User $targetUser */
        $targetUser = User::factory()->create([
            'name' => 'Delete Me',
            'email' => 'delete.me@crm.com',
            'type' => UserType::SalesAgent,
        ]);

        $this->actingAs($adminUser);

        Livewire::test(ListUsers::class)
            ->assertTableActionVisible('delete', $targetUser)
            ->callTableAction('delete', $targetUser)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_user_without_delete_users_permission_cannot_delete_users(): void
    {
        $this->seed(PermissionSeeder::class);

        /** @var User $salesAgent */
        $salesAgent = User::factory()->create([
            'type' => UserType::SalesAgent,
            'is_active' => true,
        ]);
        $salesAgent->givePermissionTo('view_users');

        /** @var User $targetUser */
        $targetUser = User::factory()->create([
            'name' => 'Protected User',
            'email' => 'protected@crm.com',
            'type' => UserType::SalesAgent,
        ]);

        $this->actingAs($salesAgent);

        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('delete', $targetUser);
    }

    public function test_user_without_view_users_permission_cannot_access_user_resource(): void
    {
        /** @var User $unauthorizedUser */
        $unauthorizedUser = User::factory()->create([
            'type' => UserType::SalesAgent,
            'is_active' => true,
        ]);

        $this->actingAs($unauthorizedUser);

        $response = $this->get(UserResource::getUrl('index'));
        $response->assertForbidden();
    }

    public function test_user_without_edit_users_permission_cannot_see_edit_action(): void
    {
        $this->seed(PermissionSeeder::class);

        /** @var User $viewerUser */
        $viewerUser = User::factory()->create([
            'type' => UserType::SalesAgent,
            'is_active' => true,
        ]);
        $viewerUser->givePermissionTo('view_users');

        /** @var User $targetUser */
        $targetUser = User::factory()->create([
            'name' => 'Target User',
            'email' => 'target.user@crm.com',
            'type' => UserType::SalesAgent,
        ]);

        $this->actingAs($viewerUser);

        // Edit action must be hidden in listing table
        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('edit', $targetUser);

        // Edit action must be hidden in View page header actions
        Livewire::test(ViewUser::class, ['record' => $targetUser->getKey()])
            ->assertActionHidden('edit');

        // Direct URL to edit page must return 403 Forbidden
        $response = $this->get(UserResource::getUrl('edit', ['record' => $targetUser]));
        $response->assertForbidden();
    }

    public function test_user_without_create_users_permission_cannot_see_create_action_or_access_create_page(): void
    {
        $this->seed(PermissionSeeder::class);

        /** @var User $viewerUser */
        $viewerUser = User::factory()->create([
            'type' => UserType::SalesAgent,
            'is_active' => true,
        ]);
        $viewerUser->givePermissionTo('view_users');

        $this->actingAs($viewerUser);

        // "New user" (create) header action must be hidden in listing
        Livewire::test(ListUsers::class)
            ->assertActionHidden('create');

        // Direct URL to create page must return 403 Forbidden
        $response = $this->get(UserResource::getUrl('create'));
        $response->assertForbidden();
    }

    public function test_user_with_create_users_permission_can_see_create_action_and_access_create_page(): void
    {
        $this->seed(PermissionSeeder::class);

        /** @var User $creatorUser */
        $creatorUser = User::factory()->create([
            'type' => UserType::SalesAgent,
            'is_active' => true,
        ]);
        $creatorUser->givePermissionTo(['view_users', 'create_users']);

        $this->actingAs($creatorUser);

        // "New user" (create) header action must be visible
        Livewire::test(ListUsers::class)
            ->assertActionVisible('create');

        // Direct URL to create page must be accessible
        $response = $this->get(UserResource::getUrl('create'));
        $response->assertOk();
    }
}

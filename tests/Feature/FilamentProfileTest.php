<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Profile;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_profile_page(): void
    {
        $response = $this->get(Profile::getUrl());
        $response->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_authenticated_user_can_access_profile_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get(Profile::getUrl());
        $response->assertOk();
    }

    public function test_profile_active_tab_can_be_set_from_query_parameter(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::withQueryParams(['tab' => '2fa'])
            ->test(Profile::class)
            ->assertSet('activeTab', '2fa');

        Livewire::withQueryParams(['tab' => 'password'])
            ->test(Profile::class)
            ->assertSet('activeTab', 'password');
    }

    public function test_user_can_update_profile_info_name_and_email(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', 'New John Doe')
            ->set('email', 'new.john@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('New John Doe', $user->name);
        $this->assertSame('new.john@example.com', $user->email);
    }

    public function test_user_can_upload_profile_image_which_is_converted_to_webp(): void
    {
        Storage::fake('public');

        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($user);

        // Upload a sample PNG image
        $file = UploadedFile::fake()->image('avatar.png', 100, 100);

        Livewire::test(Profile::class)
            ->set('image', $file)
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->image);
        $this->assertStringEndsWith('.webp', $user->image);

        // Verify the file exists on the public disk
        Storage::disk('public')->assertExists($user->image);

        // Verify that the file content is a valid WebP image (starts with RIFF....WEBP)
        $content = Storage::disk('public')->get($user->image);
        $this->assertStringStartsWith('RIFF', $content);
        $this->assertStringContainsString('WEBP', substr($content, 0, 16));
    }

    public function test_user_cannot_upload_profile_image_greater_than_3mb(): void
    {
        Storage::fake('public');

        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($user);

        // 3.5MB file (exceeds 3072 KB)
        $oversizedFile = UploadedFile::fake()->create('huge-photo.jpg', 3600, 'image/jpeg');

        Livewire::test(Profile::class)
            ->set('image', $oversizedFile)
            ->call('updateProfile')
            ->assertHasErrors(['image']);

        $user->refresh();
        $this->assertNull($user->image);
    }

    public function test_user_can_update_password_with_valid_current_password(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => 'old-secret-password',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('current_password', 'old-secret-password')
            ->set('password', 'new-super-secret-password')
            ->set('password_confirmation', 'new-super-secret-password')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('new-super-secret-password', $user->password));
    }

    public function test_user_cannot_update_password_with_invalid_current_password(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => 'actual-password',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('current_password', 'wrong-current-password')
            ->set('password', 'new-password-1234')
            ->set('password_confirmation', 'new-password-1234')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        $user->refresh();
        $this->assertTrue(Hash::check('actual-password', $user->password));
    }

    public function test_user_can_enable_two_factor_authentication_directly(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
            '2fa_enabled' => false,
            '2fa_secret' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(Profile::class)
            ->call('switchTab', '2fa')
            ->call('enableTwoFactor')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertTrue($user->hasConfirmedTwoFactor());
        $this->assertNotEmpty($user->getAttribute('2fa_secret'));
        $this->assertSame(32, strlen($user->getAttribute('2fa_secret')));
        $this->assertSame($user->getAttribute('2fa_secret'), $component->get('two_factor_secret'));
        $this->assertNotNull($component->get('qrCodeSvg'));
    }

    public function test_user_can_disable_two_factor_authentication(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
            '2fa_enabled' => true,
            '2fa_secret' => 'SECRETKEY123456',
            '2fa_confirmed_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->call('switchTab', '2fa')
            ->call('disableTwoFactor')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertFalse($user->hasTwoFactorEnabled());
        $this->assertNull($user->getAttribute('2fa_secret'));
    }

    public function test_login_with_two_factor_enabled_and_empty_secret_redirects_to_profile_2fa_tab(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'empty-secret-2fa@example.com',
            'password' => 'secret123',
            'is_active' => true,
            '2fa_enabled' => true,
            '2fa_secret' => null, // 2FA is enabled but secret is empty!
        ]);

        Livewire::test(Login::class)
            ->set('data.email', 'empty-secret-2fa@example.com')
            ->set('data.password', 'secret123')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(Profile::getUrl(['tab' => '2fa']));

        // User must be authenticated without OTP!
        $this->assertAuthenticatedAs($user);

        // Session must be recorded
        $this->assertDatabaseHas('user_sessions', [
            'user_id' => $user->id,
            'is_active' => true,
        ]);
    }

    public function test_profile_generates_and_saves_secret_when_2fa_enabled_and_secret_is_empty(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
            '2fa_enabled' => true,
            '2fa_secret' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(Profile::class)
            ->call('switchTab', '2fa');

        $user->refresh();
        $this->assertNotNull($user->getAttribute('2fa_secret'));
        $this->assertSame(32, strlen($user->getAttribute('2fa_secret')));
        $this->assertSame($user->getAttribute('2fa_secret'), $component->get('two_factor_secret'));
        $this->assertNotNull($component->get('qrCodeSvg'));
    }

    public function test_user_session_is_updated_on_logout(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $session = UserSession::factory()->create([
            'user_id' => $user->id,
            'session_identifier' => 'test-logout-session-id',
            'is_active' => true,
            'logout_at' => null,
            'revoked_at' => null,
        ]);

        $this->actingAs($user);
        session(['user_session_id' => 'test-logout-session-id']);

        $response = $this->post(route('filament.admin.auth.logout'));
        $response->assertRedirect(route('filament.admin.auth.login'));

        $session->refresh();
        $this->assertFalse($session->is_active);
        $this->assertNotNull($session->logout_at);
        $this->assertNotNull($session->revoked_at);
    }

    public function test_sessions_tab_renders_user_sessions_with_cairo_time_and_current_session_identifier(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $currentSession = UserSession::factory()->create([
            'user_id' => $user->id,
            'session_identifier' => 'current-device-uuid',
            'browser_name' => 'Chrome',
            'browser_version' => '128.0',
            'os_name' => 'macOS',
            'device_type' => 'desktop',
            'ip_address' => '197.34.12.90',
            'city' => 'Cairo',
            'country' => 'Egypt',
            'login_at' => Carbon::parse('2026-09-20 10:30:00', 'UTC'),
            'last_activity_at' => Carbon::parse('2026-09-20 12:15:00', 'UTC'),
            'is_active' => true,
        ]);

        $otherSession = UserSession::factory()->create([
            'user_id' => $user->id,
            'session_identifier' => 'other-phone-uuid',
            'browser_name' => 'Safari',
            'browser_version' => '17.0',
            'os_name' => 'iOS',
            'device_type' => 'mobile',
            'ip_address' => '41.233.10.15',
            'city' => 'Alexandria',
            'country' => 'Egypt',
            'login_at' => Carbon::parse('2026-09-19 18:00:00', 'UTC'),
            'last_activity_at' => Carbon::parse('2026-09-19 19:30:00', 'UTC'),
            'is_active' => true,
        ]);

        $this->actingAs($user);
        session(['user_session_id' => 'current-device-uuid']);

        $component = Livewire::test(Profile::class)
            ->call('switchTab', 'sessions');

        $component->assertSee('Active Browser');
        $component->assertSee('This Device (Current Session)');
        $component->assertSee('Chrome');
        $component->assertSee('Safari');
        $component->assertSee('197.34.12.90');
        $component->assertSee('41.233.10.15');

        // Check Cairo time formatting (10:30 UTC = 13:30 / 01:30 PM Cairo in DST +3)
        // Check that AM or PM is present
        $formattedTime = $component->instance()->formatCairoTime($currentSession->login_at);
        $this->assertTrue(str_contains($formattedTime, 'AM') || str_contains($formattedTime, 'PM'));
        $component->assertSee($formattedTime);
    }

    public function test_user_can_logout_specific_session(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $currentSession = UserSession::factory()->create([
            'user_id' => $user->id,
            'session_identifier' => 'my-current-session',
            'is_active' => true,
            'logout_at' => null,
            'revoked_at' => null,
        ]);

        $otherSession = UserSession::factory()->create([
            'user_id' => $user->id,
            'session_identifier' => 'remote-device-session',
            'is_active' => true,
            'logout_at' => null,
            'revoked_at' => null,
        ]);

        $this->actingAs($user);
        session(['user_session_id' => 'my-current-session']);

        Livewire::test(Profile::class)
            ->call('switchTab', 'sessions')
            ->call('logoutSession', $otherSession->id)
            ->assertHasNoErrors()
            ->assertNotified('Session logged out successfully.');

        $otherSession->refresh();
        $this->assertFalse($otherSession->is_active);
        $this->assertNotNull($otherSession->logout_at);
        $this->assertNotNull($otherSession->revoked_at);

        $currentSession->refresh();
        $this->assertTrue($currentSession->is_active);
    }

    public function test_user_logging_out_current_session_redirects_to_login(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $currentSession = UserSession::factory()->create([
            'user_id' => $user->id,
            'session_identifier' => 'self-logout-session',
            'is_active' => true,
        ]);

        $this->actingAs($user);
        session(['user_session_id' => 'self-logout-session']);

        Livewire::test(Profile::class)
            ->call('switchTab', 'sessions')
            ->call('logoutSession', $currentSession->id)
            ->assertRedirect(route('filament.admin.auth.login'));

        $currentSession->refresh();
        $this->assertFalse($currentSession->is_active);
        $this->assertNotNull($currentSession->logout_at);
        $this->assertNotNull($currentSession->revoked_at);
    }
}

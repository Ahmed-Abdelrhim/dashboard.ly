<?php

namespace Tests\Feature;

use App\Actions\Auth\StoreUserSessionAction;
use App\Enums\AuthenticationType;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\TwoFactorChallenge;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use ipinfo\ipinfo\Details;
use Livewire\Livewire;
use PragmaRX\Google2FALaravel\Google2FA;
use Tests\TestCase;

class FilamentTwoFactorAndSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_two_factor_can_login_directly_and_creates_user_session_with_ip(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'direct-login@example.com',
            'password' => 'secret123',
            'is_active' => true,
            '2fa_enabled' => false,
        ]);

        $this->assertGuest();

        Livewire::withQueryParams([])
            ->test(Login::class)
            ->set('data.email', 'direct-login@example.com')
            ->set('data.password', 'secret123')
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);

        // Verify user session was stored in user_sessions table
        /** @var UserSession|null $userSession */
        $userSession = UserSession::where('user_id', $user->id)->first();
        $this->assertNotNull($userSession);
        $this->assertSame(AuthenticationType::SessionCookie, $userSession->authentication_type);
        $this->assertTrue($userSession->isActive());
        $this->assertNotNull($userSession->ip_address);
        $this->assertSame(session('user_session_id'), $userSession->session_identifier);
    }

    public function test_user_with_two_factor_enabled_is_redirected_to_challenge_and_not_logged_in(): void
    {
        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();

        /** @var User $user */
        $user = User::factory()->create([
            'email' => '2fa-user@example.com',
            'password' => 'secret123',
            'is_active' => true,
            '2fa_enabled' => true,
            '2fa_secret' => $secret,
            '2fa_confirmed_at' => now(),
        ]);

        Livewire::test(Login::class)
            ->set('data.email', '2fa-user@example.com')
            ->set('data.password', 'secret123')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('filament.admin.auth.two-factor-challenge'));

        // User must NOT be logged in yet!
        $this->assertGuest();

        // 2FA pending state must be in session
        $this->assertSame($user->id, session('auth.2fa.user_id'));

        // No user_session record should be created yet
        $this->assertDatabaseMissing('user_sessions', [
            'user_id' => $user->id,
        ]);
    }

    public function test_unauthenticated_guest_cannot_access_two_factor_challenge_page(): void
    {
        // Visiting the challenge page without auth.2fa.user_id in session
        $response = $this->get(route('filament.admin.auth.two-factor-challenge'));

        $response->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_already_authenticated_user_cannot_access_two_factor_challenge_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($user);

        // A fully authenticated user cannot access the 2FA challenge page
        $response = $this->get(route('filament.admin.auth.two-factor-challenge'));

        $response->assertRedirect(route('filament.admin.pages.dashboard'));
    }

    public function test_two_factor_challenge_fails_with_invalid_otp(): void
    {
        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();

        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'otp-fail@example.com',
            'is_active' => true,
            '2fa_enabled' => true,
            '2fa_secret' => $secret,
            '2fa_confirmed_at' => now(),
        ]);

        // Place user in pending 2FA state
        session()->put('auth.2fa.user_id', $user->id);

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', '000000')
            ->call('authenticate')
            ->assertHasErrors(['data.code']);

        // User still guest
        $this->assertGuest();

        // No session stored
        $this->assertDatabaseMissing('user_sessions', [
            'user_id' => $user->id,
        ]);
    }

    public function test_two_factor_challenge_page_renders_six_digit_boxes_ui(): void
    {
        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();

        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'otp-boxes@example.com',
            'is_active' => true,
            '2fa_enabled' => true,
            '2fa_secret' => $secret,
            '2fa_confirmed_at' => now(),
        ]);

        $this->withSession(['auth.2fa.user_id' => $user->id]);

        $response = $this->get(route('filament.admin.auth.two-factor-challenge'));
        $response->assertOk();
        $response->assertSee('x-ref="otp_0"', false);
        $response->assertSee('x-ref="otp_5"', false);
        $response->assertSee('6-digit authentication code');
    }

    public function test_two_factor_challenge_validates_six_numeric_digits(): void
    {
        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();

        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'otp-validation@example.com',
            'is_active' => true,
            '2fa_enabled' => true,
            '2fa_secret' => $secret,
            '2fa_confirmed_at' => now(),
        ]);

        session()->put('auth.2fa.user_id', $user->id);

        // Required
        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', '')
            ->call('authenticate')
            ->assertHasErrors(['data.code' => 'required']);

        // Less than 6 digits
        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', '123')
            ->call('authenticate')
            ->assertHasErrors(['data.code' => 'digits']);

        // Non numeric
        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', 'abcdef')
            ->call('authenticate')
            ->assertHasErrors(['data.code']);
    }

    public function test_two_factor_challenge_succeeds_with_valid_otp_and_creates_user_session(): void
    {
        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();
        $validOtp = $google2fa->getCurrentOtp($secret);

        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'otp-success@example.com',
            'is_active' => true,
            '2fa_enabled' => true,
            '2fa_secret' => $secret,
            '2fa_confirmed_at' => now(),
        ]);

        // Place user in pending 2FA state
        session()->put('auth.2fa.user_id', $user->id);

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', $validOtp)
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('filament.admin.pages.dashboard'));

        // User is now authenticated
        $this->assertAuthenticatedAs($user);

        // Pending session cleared
        $this->assertNull(session('auth.2fa.user_id'));

        // user_session record created
        /** @var UserSession|null $userSession */
        $userSession = UserSession::where('user_id', $user->id)->first();
        $this->assertNotNull($userSession);
        $this->assertTrue($userSession->isActive());
        $this->assertSame(AuthenticationType::SessionCookie, $userSession->authentication_type);
        $this->assertSame(session('user_session_id'), $userSession->session_identifier);
    }

    public function test_user_cannot_go_back_to_two_factor_challenge_page_after_successful_login(): void
    {
        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();
        $validOtp = $google2fa->getCurrentOtp($secret);

        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'otp-back-test@example.com',
            'is_active' => true,
            '2fa_enabled' => true,
            '2fa_secret' => $secret,
            '2fa_confirmed_at' => now(),
        ]);

        // Put in 2FA session and verify
        session()->put('auth.2fa.user_id', $user->id);

        Livewire::test(TwoFactorChallenge::class)
            ->set('data.code', $validOtp)
            ->call('authenticate');

        $this->assertAuthenticatedAs($user);

        // Trying to access the challenge page again should redirect away to dashboard
        $response = $this->get(route('filament.admin.auth.two-factor-challenge'));
        $response->assertRedirect(route('filament.admin.pages.dashboard'));
    }

    public function test_admin_revoking_session_expires_and_logs_out_user_on_subsequent_request(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($user);

        // First request establishes session
        $response = $this->get(route('filament.admin.pages.dashboard'));
        $response->assertOk();

        $sessionId = session('user_session_id');
        $this->assertNotNull($sessionId);

        /** @var UserSession|null $userSession */
        $userSession = UserSession::where('session_identifier', $sessionId)->first();
        $this->assertNotNull($userSession);
        $this->assertTrue($userSession->isActive());

        // Admin revokes this session
        $userSession->revoke();
        $this->assertTrue($userSession->fresh()->isRevoked());

        // Subsequent request by user hits EnsureUserSessionNotRevoked middleware
        $subsequentResponse = $this->get(route('filament.admin.pages.dashboard'));

        $subsequentResponse->assertRedirect(route('filament.admin.auth.login'));
        $this->assertGuest();
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'wrong-pass@example.com',
            'password' => 'secret123',
            'is_active' => true,
        ]);

        Livewire::test(Login::class)
            ->set('data.email', 'wrong-pass@example.com')
            ->set('data.password', 'wrongpassword')
            ->call('authenticate')
            ->assertHasErrors(['data.email']);

        $this->assertGuest();
    }

    public function test_inactive_user_with_two_factor_cannot_login(): void
    {
        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();

        User::factory()->create([
            'email' => 'inactive-2fa@example.com',
            'password' => 'secret123',
            'is_active' => false,
            '2fa_enabled' => true,
            '2fa_secret' => $secret,
            '2fa_confirmed_at' => now(),
        ]);

        Livewire::test(Login::class)
            ->set('data.email', 'inactive-2fa@example.com')
            ->set('data.password', 'secret123')
            ->call('authenticate')
            ->assertHasErrors(['data.email']);

        $this->assertGuest();
    }

    public function test_store_user_session_action_stores_device_and_geolocation(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $request = Request::create('/admin', 'GET', [], [], [], [
            'REMOTE_ADDR' => '198.51.100.42',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ]);

        // Mock ipinfo attribute as provided by ipinfolaravel middleware
        $details = new Details([
            'ip' => '198.51.100.42',
            'city' => 'Amsterdam',
            'country' => 'NL',
            'country_code' => 'NL',
            'country_name' => 'Netherlands',
        ]);
        $request->attributes->set('ipinfo', $details);

        $action = app(StoreUserSessionAction::class);
        $session = $action->execute($user, $request);

        $this->assertSame('198.51.100.42', $session->ip_address);
        $this->assertSame('Macintosh', $session->device_name);
        $this->assertSame('desktop', $session->device_type);
        $this->assertSame('macOS', $session->os_name);
        $this->assertSame('Chrome', $session->browser_name);
        $this->assertSame('Amsterdam', $session->city);
        $this->assertSame('Netherlands', $session->country);
        $this->assertSame('NL', $session->country_code);
        $this->assertTrue($session->isActive());
    }
}

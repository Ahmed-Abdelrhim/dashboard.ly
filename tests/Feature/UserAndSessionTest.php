<?php

namespace Tests\Feature;

use App\Enums\AuthenticationType;
use App\Enums\UserType;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserAndSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_default_attributes(): void
    {
        $user = User::factory()->create([
            'email' => 'agent@example.com',
        ]);

        $this->assertSame(UserType::SalesAgent, $user->type);
        $this->assertTrue($user->isActive());
        $this->assertFalse($user->hasTwoFactorEnabled());
        $this->assertFalse($user->hasConfirmedTwoFactor());
        $this->assertTrue($user->isSalesAgent());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_user_two_factor_lifecycle(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->assertFalse($user->hasTwoFactorEnabled());

        $user->enableTwoFactor('SECRET_KEY_12345');
        $user->refresh();

        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertFalse($user->hasConfirmedTwoFactor());
        $this->assertSame('SECRET_KEY_12345', $user->getAttribute('2fa_secret'));

        $user->confirmTwoFactor();
        $user->refresh();

        $this->assertTrue($user->hasConfirmedTwoFactor());
        $this->assertNotNull($user->getAttribute('2fa_confirmed_at'));

        $user->disableTwoFactor();
        $user->refresh();

        $this->assertFalse($user->hasTwoFactorEnabled());
        $this->assertFalse($user->hasConfirmedTwoFactor());
        $this->assertNull($user->getAttribute('2fa_secret'));
    }

    public function test_user_scopes_filter_properly(): void
    {
        User::factory()->superAdmin()->create();
        User::factory()->admin()->create();
        User::factory()->salesAgent()->create();
        User::factory()->inactive()->create();
        User::factory()->withTwoFactor()->create();

        $this->assertCount(1, User::superAdmins()->get());
        $this->assertCount(1, User::admins()->get());
        $this->assertCount(3, User::salesAgents()->get());
        $this->assertCount(4, User::active()->get());
        $this->assertCount(1, User::inactive()->get());
        $this->assertCount(1, User::twoFactorEnabled()->get());
    }

    public function test_user_session_creation_and_helpers(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var UserSession $session */
        $session = UserSession::factory()->for($user)->create([
            'session_identifier' => (string) Str::uuid(),
            'authentication_type' => AuthenticationType::SessionCookie,
            'browser_name' => 'Chrome',
            'os_name' => 'macOS',
            'device_name' => 'MacBook Pro',
            'city' => 'San Francisco',
            'country' => 'United States',
        ]);

        $this->assertTrue($session->isActive());
        $this->assertFalse($session->isRevoked());
        $this->assertTrue($session->isSessionCookie());
        $this->assertFalse($session->isBearerToken());
        $this->assertSame('Chrome on macOS on MacBook Pro', $session->deviceSummary());
        $this->assertSame('San Francisco, United States', $session->locationSummary());

        $session->recordActivity('10.0.0.1');
        $session->refresh();

        $this->assertSame('10.0.0.1', $session->last_activity_ip);
        $this->assertNotNull($session->last_activity_at);

        $session->markLoggedOut();
        $session->refresh();

        $this->assertFalse($session->isActive());
        $this->assertNotNull($session->logout_at);
    }

    public function test_user_session_with_sanctum_token_and_cascade_delete(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $token = $user->createToken('auth-token');

        /** @var UserSession $session */
        $session = UserSession::factory()->for($user)->bearerToken($token->accessToken->id)->create([
            'session_identifier' => (string) Str::uuid(),
        ]);

        $this->assertTrue($session->isBearerToken());
        $this->assertNotNull($session->sanctumToken);
        $this->assertSame('auth-token', $session->sanctumToken->name);

        $session->revoke();
        $session->refresh();

        $this->assertTrue($session->isRevoked());
        $this->assertFalse($session->isActive());

        // When sanctum token is deleted, foreign key constraint cascades and deletes the user session
        $token->accessToken->delete();
        $this->assertDatabaseMissing('user_sessions', ['id' => $session->id]);

        // When user is deleted, remaining sessions are deleted via cascade
        $userSession = UserSession::factory()->for($user)->create();
        $sessionId = $userSession->id;
        $user->delete();

        $this->assertDatabaseMissing('user_sessions', ['id' => $sessionId]);
    }
}

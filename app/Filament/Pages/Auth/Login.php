<?php

namespace App\Filament\Pages\Auth;

use App\Actions\Auth\StoreUserSessionAction;
use App\Filament\Pages\Profile;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();
        $authProvider = $authGuard->getProvider();
        $credentials = $this->getCredentialsFromFormData($data);
        $remember = (bool) ($data['remember'] ?? false);

        $this->fireAttemptingEvent($authGuard, $credentials, $remember);

        /** @var User|null $user */
        $user = $authProvider->retrieveByCredentials($credentials);

        if (! $user || ! $authProvider->validateCredentials($user, $credentials)) {
            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        if (! $this->isUserAllowedToAccessPanel($user)) {
            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        // If Two-Factor Authentication is enabled
        if ($user->hasTwoFactorEnabled()) {
            if (empty($user->getAttribute('2fa_secret'))) {
                // If 2fa_enabled is true but 2fa_secret is empty, allow login and redirect to Profile 2FA Tab
                $authGuard->login($user, $remember);
                session()->regenerate();

                app(StoreUserSessionAction::class)->execute($user, request());
                $user->recordLogin();

                $profile2faUrl = Profile::getUrl(['tab' => '2fa']);

                return new class($profile2faUrl) implements LoginResponse
                {
                    public function __construct(protected string $url) {}

                    public function toResponse($request): RedirectResponse|Redirector
                    {
                        return redirect()->to($this->url);
                    }
                };
            }

            session()->put('auth.2fa.user_id', $user->getAuthIdentifier());
            session()->put('auth.2fa.remember', $remember);

            $challengeUrl = route('filament.admin.auth.two-factor-challenge');

            return new class($challengeUrl) implements LoginResponse
            {
                public function __construct(protected string $url) {}

                public function toResponse($request): RedirectResponse|Redirector
                {
                    return redirect()->to($this->url);
                }
            };
        }

        // Standard login when 2FA is not enabled
        $authGuard->login($user, $remember);
        session()->regenerate();

        // Store user session based on IP
        app(StoreUserSessionAction::class)->execute($user, request());
        $user->recordLogin();

        return app(LoginResponse::class);
    }
}

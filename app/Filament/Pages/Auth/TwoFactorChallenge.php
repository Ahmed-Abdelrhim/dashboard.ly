<?php

namespace App\Filament\Pages\Auth;

use App\Actions\Auth\StoreUserSessionAction;
use App\Filament\Forms\Components\OtpInput;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FALaravel\Google2FA;

/**
 * @property-read Schema $form
 */
class TwoFactorChallenge extends SimplePage
{
    use RestrictsFileUploadsToSchemaComponents;
    use WithRateLimiting;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        // If the user is already authenticated, they cannot access the OTP page again
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());

            return;
        }

        // If the user hasn't successfully entered valid credentials first, reject access
        if (! session()->has('auth.2fa.user_id')) {
            redirect()->to(Filament::getLoginUrl());

            return;
        }

        $user = User::find(session('auth.2fa.user_id'));
        if (! $user || ! $user->isActive() || ! $user->hasTwoFactorEnabled()) {
            session()->forget(['auth.2fa.user_id', 'auth.2fa.remember']);
            redirect()->to(Filament::getLoginUrl());

            return;
        }

        $this->form->fill();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                OtpInput::make('code')
                    ->hiddenLabel()
                    ->length(6)
                    ->required()
                    ->autofocus()
                    ->extraFieldWrapperAttributes([
                        'class' => 'text-center flex flex-col items-center justify-center',
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('authenticate')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),
            ]);
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Verify & Log In')
            ->submit('authenticate');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getFormActionsAlignment(): string|Alignment
    {
        return Alignment::Center;
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $code = trim((string) ($data['code'] ?? ''));

        $userId = session()->get('auth.2fa.user_id');

        if (! $userId) {
            redirect()->to(Filament::getLoginUrl());

            return null;
        }

        /** @var User|null $user */
        $user = User::find($userId);

        if (! $user || ! $user->isActive() || ! $user->hasTwoFactorEnabled()) {
            session()->forget(['auth.2fa.user_id', 'auth.2fa.remember']);
            redirect()->to(Filament::getLoginUrl());

            return null;
        }

        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');
        $secret = $user->getAttribute('2fa_secret');

        $isValid = false;
        if (! empty($secret) && ! empty($code)) {
            $isValid = $google2fa->verifyKey($secret, $code) !== false;
        }

        if (! $isValid) {
            throw ValidationException::withMessages([
                'data.code' => 'The provided two-factor authentication code is invalid.',
            ]);
        }

        $remember = (bool) session()->get('auth.2fa.remember', false);
        session()->forget(['auth.2fa.user_id', 'auth.2fa.remember']);

        Filament::auth()->login($user, $remember);
        session()->regenerate();

        // Store user session based on user's IP and request
        app(StoreUserSessionAction::class)->execute($user, request());
        $user->recordLogin();

        return app(LoginResponse::class);
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title("Too many verification attempts. Please try again in {$exception->secondsUntilAvailable} seconds.")
            ->danger();
    }

    public function getTitle(): string|Htmlable
    {
        return 'Two-Factor Authentication';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Two-Factor Challenge';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Enter the 6-digit verification code from Google Authenticator APP.';
    }
}

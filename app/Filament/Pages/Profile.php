<?php

namespace App\Filament\Pages;

use App\Actions\Auth\StoreUserSessionAction;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Pages\PageConfiguration;
use Filament\Panel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Livewire\WithFileUploads;
use PragmaRX\Google2FAQRCode\Google2FA;

class Profile extends Page
{
    use WithFileUploads;

    protected string $view = 'filament.pages.profile';

    protected static ?string $title = 'Profile';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static bool $shouldRegisterNavigation = false;

    protected static bool $isDiscovered = false;

    public static function getRelativeRouteName(Panel $panel): string
    {
        return 'profile';
    }

    public static function getLabel(): string
    {
        return 'Profile';
    }

    public static function getRouteName(?Panel $panel = null): string
    {
        $panel ??= Filament::getCurrentOrDefaultPanel();

        return $panel->generateRouteName('auth.profile');
    }

    public static function registerRoutes(Panel $panel, ?PageConfiguration $configuration = null): void
    {
        static::routes($panel, $configuration);
    }

    public string $activeTab = 'profile';

    // Tab 1: Profile Info
    public string $name = '';

    public string $email = '';

    public $image = null;

    public ?string $current_image = null;

    // Tab 2: Password Update
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    // Tab 3: Two-Factor Authentication
    public bool $two_factor_enabled = false;

    public ?string $two_factor_secret = null;

    public ?string $new_2fa_secret = null;

    public string $confirmation_code = '';

    public function mount(): void
    {
        $user = auth()->user();

        $tab = request()->query('tab');
        if (in_array($tab, ['profile', 'password', '2fa', 'sessions'], true)) {
            $this->activeTab = $tab;
        }

        $this->name = $user->name;
        $this->email = $user->email;
        $this->current_image = $user->image;

        $this->two_factor_enabled = $user->hasTwoFactorEnabled();
        $this->two_factor_secret = $user->getAttribute('2fa_secret');

        // If 2FA is enabled but has no secret, generate secret and save to database
        if ($this->two_factor_enabled && empty($this->two_factor_secret)) {
            $google2fa = (new Google2FA);
            $generatedSecret = $google2fa->generateSecretKey(32);
            $user->forceFill(['2fa_secret' => $generatedSecret])->save();
            $this->two_factor_secret = $generatedSecret;
        }
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['profile', 'password', '2fa', 'sessions'], true)) {
            $this->activeTab = $tab;

            if ($tab === '2fa') {
                $user = auth()->user();
                $this->two_factor_enabled = $user->hasTwoFactorEnabled();
                $this->two_factor_secret = $user->getAttribute('2fa_secret');

                if ($this->two_factor_enabled && empty($this->two_factor_secret)) {
                    $google2fa = (new Google2FA);
                    $generatedSecret = $google2fa->generateSecretKey(32);
                    $user->forceFill(['2fa_secret' => $generatedSecret])->save();
                    $this->two_factor_secret = $generatedSecret;
                }
            }
        }
    }

    public function updateProfile(): void
    {
        $user = auth()->user();

        $this->validate([
            'name' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'image' => ['nullable', 'image', 'max:3072'], // Max 3MB (3072 KB)
        ]);

        if ($this->image) {
            $manager = new ImageManager(new Driver);
            $img = $manager->decodePath($this->image->getRealPath());
            $encoded = $img->encodeUsingFormat(Format::WEBP, quality: 85);

            $filename = 'avatars/'.Str::uuid().'.webp';
            Storage::disk('public')->put($filename, (string) $encoded);

            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            $user->image = $filename;
            $this->current_image = $filename;
            $this->image = null;
        }

        $user->name = $this->name;
        $user->email = $this->email;
        $user->save();

        Notification::make()->title('Profile updated successfully.')->success()->send();
    }

    public function removeImage(): void
    {
        $user = auth()->user();

        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

        $user->image = null;
        $user->save();

        $this->current_image = null;
        $this->image = null;

        Notification::make()
            ->title('Profile photo removed.')
            ->info()
            ->send();
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($this->password),
        ]);

        $this->current_password = '';
        $this->password = '';
        $this->password_confirmation = '';

        Notification::make()
            ->title('Password updated successfully.')
            ->success()
            ->send();
    }

    public function enableTwoFactor(): void
    {
        $google2fa = (new Google2FA);
        $secret = $google2fa->generateSecretKey(32);

        $user = auth()->user();
        $user->forceFill([
            '2fa_enabled' => true,
            '2fa_secret' => $secret,
            '2fa_confirmed_at' => now(),
        ])->save();

        $this->two_factor_enabled = true;
        $this->two_factor_secret = $secret;
        $this->new_2fa_secret = null;
        $this->confirmation_code = '';

        Notification::make()
            ->title('Two-Factor Authentication enabled successfully.')
            ->success()
            ->send();
    }

    public function startTwoFactorSetup(): void
    {
        $this->enableTwoFactor();
    }

    public function cancelTwoFactorSetup(): void
    {
        $this->new_2fa_secret = null;
        $this->confirmation_code = '';
    }

    public function confirmTwoFactor(): void
    {
        $this->enableTwoFactor();
    }

    public function disableTwoFactor(): void
    {
        $user = auth()->user();
        $user->disableTwoFactor();

        $this->two_factor_enabled = false;
        $this->two_factor_secret = null;
        $this->new_2fa_secret = null;
        $this->confirmation_code = '';

        Notification::make()
            ->title('Two-Factor Authentication disabled.')
            ->warning()
            ->send();
    }

    public function getQrCodeSvgProperty(): ?string
    {
        $user = auth()->user();

        if ($this->two_factor_enabled && empty($this->two_factor_secret) && $user) {
            $google2fa = (new Google2FA);
            $generatedSecret = $google2fa->generateSecretKey(32);
            $user->forceFill(['2fa_secret' => $generatedSecret])->save();
            $this->two_factor_secret = $generatedSecret;
        }

        $secret = $this->two_factor_secret ?: $this->new_2fa_secret;

        if (empty($secret)) {
            return null;
        }

        $google2fa = new Google2FA;

        return $google2fa->getQRCodeInline(
            config('app.name', 'WaveX CRM'),
            $user ? $user->email : 'user@example.com',
            $secret
        );
    }

    public function getUserSessionsProperty()
    {
        return auth()->user()
            ?->sessions()
            ->orderByDesc('is_active')
            ->orderByDesc('last_activity_at')
            ->orderByDesc('login_at')
            ->get() ?? collect();
    }

    public function getCurrentSessionIdentifierProperty(): ?string
    {
        $id = session('user_session_id') ?? (request()->hasSession() ? request()->session()->get('user_session_id') : null);

        if (! $id && auth()->check() && request()->hasSession()) {
            $userSession = app(StoreUserSessionAction::class)->execute(auth()->user(), request());
            $id = $userSession->session_identifier;
        }

        return $id;
    }

    public function logoutSession(int $sessionId): void
    {
        $user = auth()->user();
        $session = $user?->sessions()->where('id', $sessionId)->first();

        if (! $session) {
            \Log::info('session not found');
            Notification::make()
                ->title('Session not found.')
                ->danger()
                ->send();

            return;
        }

        \Log::info('Session was already found', [
            'session_id' => $session->session_identifier,
            'user_id' => $session->user_id,
        ]);

        $now = now();
        $session->update([
            'is_active' => false,
            'revoked_at' => $now,
            'logout_at' => $now,
        ]);

        if ($session->session_identifier === $this->currentSessionIdentifier) {
            \Log::info('current session '.$this->currentSessionIdentifier);

            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();

            $this->redirect(route('filament.admin.auth.login'));

            return;
        }

        Notification::make()
            ->title('Session logged out successfully.')
            ->success()
            ->send();
    }

    public function logoutOtherSessions(): void
    {
        $user = auth()->user();
        $currentId = $this->currentSessionIdentifier;
        $now = now();

        $user?->sessions()
            ->where('is_active', true)
            ->when($currentId, fn ($q) => $q->where('session_identifier', '!=', $currentId))
            ->update([
                'is_active' => false,
                'revoked_at' => $now,
                'logout_at' => $now,
            ]);

        Notification::make()
            ->title('All other sessions have been logged out.')
            ->success()
            ->send();
    }

    public function formatCairoTime(?Carbon $time): string
    {
        if (! $time) {
            return 'N/A';
        }

        return $time->timezone('Africa/Cairo')->format('M d, Y - h:i A');
    }
}

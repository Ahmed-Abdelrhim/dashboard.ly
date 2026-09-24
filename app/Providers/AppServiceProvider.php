<?php

namespace App\Providers;

use App\Enums\UserType;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability): ?bool {
            if ($user instanceof User && ($user->type === UserType::SuperAdmin || $user->hasRole('super_admin'))) {
                return true;
            }

            return null;
        });

        Event::listen(Logout::class, function (Logout $event): void {
            $sessionId = session('user_session_id') ?? (request()->hasSession() ? request()->session()->get('user_session_id') : null);
            $now = now();

            if ($sessionId) {
                UserSession::where('session_identifier', $sessionId)
                    ->update([
                        'logout_at' => $now,
                        'is_active' => false,
                        'revoked_at' => $now,
                    ]);
            } elseif ($event->user) {
                UserSession::where('user_id', $event->user->getAuthIdentifier())
                    ->where('is_active', true)
                    ->latest('login_at')
                    ->first()
                    ?->update([
                        'logout_at' => $now,
                        'is_active' => false,
                        'revoked_at' => $now,
                    ]);
            }
        });
    }
}

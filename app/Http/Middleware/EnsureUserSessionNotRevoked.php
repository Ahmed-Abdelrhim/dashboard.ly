<?php

namespace App\Http\Middleware;

use App\Actions\Auth\StoreUserSessionAction;
use App\Models\UserSession;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserSessionNotRevoked
{
    public function __construct(
        protected StoreUserSessionAction $storeUserSessionAction
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $sessionId = $request->session()->get('user_session_id');

            if ($sessionId) {
                /** @var UserSession|null $userSession */
                $userSession = UserSession::where('session_identifier', $sessionId)
                    ->where('user_id', $user->getAuthIdentifier())
                    ->first();

                if (! $userSession || ! $userSession->isActive()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    $loginUrl = function_exists('filament') && Filament::getCurrentPanel()
                        ? Filament::getLoginUrl()
                        : route('filament.admin.auth.login');

                    return redirect()->to($loginUrl)
                        ->withErrors(['email' => 'Your session has been terminated or expired by an administrator.']);
                }

                // Throttle activity updates to once every minute
                if ($userSession->last_activity_at === null || $userSession->last_activity_at->diffInSeconds(now()) >= 60) {
                    $userSession->recordActivity($request->ip());
                }
            } else {
                // If user is authenticated but no session_identifier is registered yet, initialize one
                $this->storeUserSessionAction->execute($user, $request);
            }
        }

        return $next($request);
    }
}

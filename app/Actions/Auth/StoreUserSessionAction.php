<?php

namespace App\Actions\Auth;

use App\Enums\AuthenticationType;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ipinfo\ipinfo\Details;

class StoreUserSessionAction
{
    /**
     * Store a new user session for the authenticated user based on request and IP.
     */
    public function execute(User $user, ?Request $request = null): UserSession
    {
        $request ??= request();

        $ipAddress = $request->ip() ?: '127.0.0.1';
        $userAgent = $request->userAgent() ?: 'Unknown';

        /** @var Details|null $ipinfo */
        $ipinfo = $request->attributes->get('ipinfo');

        $city = $ipinfo->city ?? null;
        $country = $ipinfo->country_name ?? $ipinfo->country ?? null;
        $countryCode = $ipinfo->country_code ?? null;

        $agentDetails = $this->parseUserAgent($userAgent);

        $sessionIdentifier = (string) Str::uuid();

        // Store the session identifier in the active session for subsequent validation and revocation checks
        if ($request->hasSession()) {
            $request->session()->put('user_session_id', $sessionIdentifier);
        }

        try {
            session()->put('user_session_id', $sessionIdentifier);
        } catch (\Throwable) {
            // Ignore if session is not active
        }

        return UserSession::create([
            'user_id' => $user->id,
            'sanctum_token_id' => null,
            'session_identifier' => $sessionIdentifier,
            'authentication_type' => AuthenticationType::SessionCookie,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_name' => $agentDetails['device_name'],
            'device_type' => $agentDetails['device_type'],
            'os_name' => $agentDetails['os_name'],
            'os_version' => $agentDetails['os_version'],
            'browser_name' => $agentDetails['browser_name'],
            'browser_version' => $agentDetails['browser_version'],
            'country' => $country,
            'country_code' => $countryCode,
            'city' => $city,
            'login_at' => now(),
            'last_activity_at' => now(),
            'last_activity_ip' => $ipAddress,
            'is_active' => true,
            'revoked_at' => null,
        ]);
    }

    /**
     * Parse device, OS, and browser from a User-Agent string.
     *
     * @return array{
     *     device_name: string|null,
     *     device_type: string,
     *     os_name: string|null,
     *     os_version: string|null,
     *     browser_name: string|null,
     *     browser_version: string|null
     * }
     */
    protected function parseUserAgent(string $userAgent): array
    {
        $browserName = null;
        $browserVersion = null;
        $osName = null;
        $osVersion = null;
        $deviceType = 'desktop';
        $deviceName = null;

        // Device Type Detection
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $userAgent)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/(mobi|iphone|ipod|blackberry|opera mini|iemobile|mobile)/i', $userAgent)) {
            $deviceType = 'mobile';
        }

        // OS Detection
        if (preg_match('/Macintosh|Mac OS X/i', $userAgent)) {
            $osName = 'macOS';
            $deviceName = 'Macintosh';
            if (preg_match('/Mac OS X ([\d_]+)/i', $userAgent, $matches)) {
                $osVersion = str_replace('_', '.', $matches[1]);
            }
        } elseif (preg_match('/Windows|Win32/i', $userAgent)) {
            $osName = 'Windows';
            $deviceName = 'Windows PC';
            if (preg_match('/Windows NT ([\d\.]+)/i', $userAgent, $matches)) {
                $osVersion = match ($matches[1]) {
                    '10.0' => '10 / 11',
                    '6.3' => '8.1',
                    '6.2' => '8',
                    '6.1' => '7',
                    default => $matches[1],
                };
            }
        } elseif (preg_match('/Android/i', $userAgent)) {
            $osName = 'Android';
            $deviceName = 'Android Device';
            if (preg_match('/Android ([\d\.]+)/i', $userAgent, $matches)) {
                $osVersion = $matches[1];
            }
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $osName = 'iOS';
            $deviceName = preg_match('/iPad/i', $userAgent) ? 'iPad' : 'iPhone';
            if (preg_match('/OS ([\d_]+)/i', $userAgent, $matches)) {
                $osVersion = str_replace('_', '.', $matches[1]);
            }
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $osName = 'Linux';
            $deviceName = 'Linux Machine';
        }

        // Browser Detection
        if (preg_match('/Edg\/([\d\.]+)/i', $userAgent, $matches)) {
            $browserName = 'Edge';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Chrome\/([\d\.]+)/i', $userAgent, $matches) && ! preg_match('/Edg|OPR/i', $userAgent)) {
            $browserName = 'Chrome';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Firefox\/([\d\.]+)/i', $userAgent, $matches)) {
            $browserName = 'Firefox';
            $browserVersion = $matches[1];
        } elseif (preg_match('/OPR\/([\d\.]+)/i', $userAgent, $matches)) {
            $browserName = 'Opera';
            $browserVersion = $matches[1];
        } elseif (preg_match('/Version\/([\d\.]+).*Safari/i', $userAgent, $matches)) {
            $browserName = 'Safari';
            $browserVersion = $matches[1];
        }

        return [
            'device_name' => $deviceName,
            'device_type' => $deviceType,
            'os_name' => $osName,
            'os_version' => $osVersion,
            'browser_name' => $browserName,
            'browser_version' => $browserVersion,
        ];
    }
}

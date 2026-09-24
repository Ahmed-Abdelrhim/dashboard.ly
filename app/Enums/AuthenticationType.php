<?php

namespace App\Enums;

enum AuthenticationType: string
{
    case BearerToken = 'bearer_token';
    case SessionCookie = 'session_cookie';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::BearerToken => 'Bearer Token',
            self::SessionCookie => 'Session Cookie',
        };
    }

    /**
     * Check if authentication is via bearer token.
     */
    public function isBearerToken(): bool
    {
        return $this === self::BearerToken;
    }

    /**
     * Check if authentication is via session cookie.
     */
    public function isSessionCookie(): bool
    {
        return $this === self::SessionCookie;
    }

    /**
     * Get all available values.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

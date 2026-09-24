<?php

namespace App\Enums;

enum UserType: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case SalesAgent = 'sales_agent';

    /**
     * Get human-readable label for the user type.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::SalesAgent => 'Sales Agent',
        };
    }

    /**
     * Check if user is Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }

    /**
     * Check if user is Admin.
     */
    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    /**
     * Check if user is Sales Agent.
     */
    public function isSalesAgent(): bool
    {
        return $this === self::SalesAgent;
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

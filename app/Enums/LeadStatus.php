<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Interested = 'interested';
    case FollowUp = 'follow_up';
    case DiscoverBooked = 'discover_booked';
    case DiscoverAttended = 'discover_attended';
    case PackageOffered = 'package_offered';
    case Won = 'won';
    case Lost = 'lost';

    /**
     * Get human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Interested => 'Interested',
            self::FollowUp => 'Follow Up',
            self::DiscoverBooked => 'Discover Booked',
            self::DiscoverAttended => 'Discover Attended',
            self::PackageOffered => 'Package Offered',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    /**
     * Get badge color for Filament / UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Contacted => 'gray',
            self::Interested => 'primary',
            self::FollowUp => 'warning',
            self::DiscoverBooked => 'purple',
            self::DiscoverAttended => 'indigo',
            self::PackageOffered => 'amber',
            self::Won => 'success',
            self::Lost => 'danger',
        };
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

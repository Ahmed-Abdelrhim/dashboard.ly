<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile Information')
                    ->schema([
                        ImageEntry::make('image')
                            ->label('Avatar')
                            ->disk('public')
                            ->visibility('public')
                            ->circular()
                            ->defaultImageUrl(fn (?User $record): ?string => $record ? filament()->getUserAvatarUrl($record) : null),

                        TextEntry::make('name')
                            ->label('Full Name')
                            ->weight('bold'),

                        TextEntry::make('email')
                            ->label('Email Address')
                            ->copyable(),

                        TextEntry::make('phone')
                            ->label('Phone Number')
                            ->placeholder('-'),

                        TextEntry::make('type')
                            ->label('System Type')
                            ->badge(),

                        TextEntry::make('roles.name')
                            ->label('Assigned Roles')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                            ->placeholder('No roles assigned'),

                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                    ])
                    ->columns(2),

                Section::make('Security & Session Activity')
                    ->schema([
                        IconEntry::make('2fa_enabled')
                            ->label('Two-Factor Authentication')
                            ->boolean(),

                        TextEntry::make('2fa_confirmed_at')
                            ->label('2FA Confirmed At')
                            ->dateTime('M d, Y - h:i A', 'Africa/Cairo')
                            ->placeholder('Not Confirmed'),

                        TextEntry::make('last_login_at')
                            ->label('Last Login')
                            ->dateTime('M d, Y - h:i A', 'Africa/Cairo')
                            ->placeholder('Never'),

                        TextEntry::make('created_at')
                            ->label('Account Created')
                            ->dateTime('M d, Y - h:i A', 'Africa/Cairo'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('M d, Y - h:i A', 'Africa/Cairo'),
                    ])
                    ->columns(2),
            ]);
    }
}

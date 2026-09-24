<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserType;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Avatar')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn (?User $record): ?string => $record ? filament()->getUserAvatarUrl($record) : null),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('type')
                    ->label('Role')
                    ->badge()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('2fa_enabled')
                    ->label('2FA')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->dateTime('M d, Y - h:i A', 'Africa/Cairo')
                    ->sortable()
                    ->placeholder('Never'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y', 'Africa/Cairo')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Role')
                    ->options(UserType::class),

                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All accounts')
                    ->trueLabel('Active users')
                    ->falseLabel('Inactive users'),

                TernaryFilter::make('2fa_enabled')
                    ->label('2FA Enabled')
                    ->placeholder('All accounts')
                    ->trueLabel('2FA Enabled')
                    ->falseLabel('2FA Disabled'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(fn (): bool => auth()->user()?->can('view_users') ?? false),
                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->can('edit_users') ?? false),
                DeleteAction::make()
                    ->visible(function (?User $record): bool {
                        /** @var User|null $authUser */
                        $authUser = auth()->user();

                        if (! $authUser || ! $authUser->can('delete_users')) {
                            return false;
                        }

                        return $record ? $record->getKey() !== $authUser->getKey() : true;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('delete_users') ?? false),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Role Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->description(fn (Role $record): string => $record->name),

                TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('users_count')
                    ->label('Assigned Users')
                    ->counts('users')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y - h:i A', 'Africa/Cairo')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(fn (Role $record): bool => $record->name === 'super_admin')
                    ->visible(fn (): bool => auth()->user()?->can('edit_roles') ?? false),
                DeleteAction::make()
                    ->hidden(fn (Role $record): bool => in_array($record->name, ['super_admin', 'admin', 'sales_agent'], true))
                    ->visible(fn (): bool => auth()->user()?->can('delete_roles') ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('delete_roles') ?? false),
                ]),
            ]);
    }
}

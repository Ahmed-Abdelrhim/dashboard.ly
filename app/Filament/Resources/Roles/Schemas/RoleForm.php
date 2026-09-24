<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role Information')
                    ->description('Define the role name and its system identification.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Role Name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (?Role $record): bool => $record?->name === 'super_admin')
                            ->helperText('Unique name identifying the role (e.g., branch_manager, supervisor).'),

                        TextInput::make('guard_name')
                            ->label('Guard Name')
                            ->default('web')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Permissions Assignment')
                    ->description('Select the permissions assigned to this role. Permissions are seeded and maintained by the system.')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => str($record->name)->replace('_', ' ')->title()->toString())
                            ->columns([
                                'sm' => 2,
                                'md' => 3,
                                'xl' => 4,
                            ])
                            ->searchable()
                            ->bulkToggleable()
                            ->disabled(fn (?Role $record): bool => $record?->name === 'super_admin')
                            ->noSearchResultsMessage('No permissions found matching your search.'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

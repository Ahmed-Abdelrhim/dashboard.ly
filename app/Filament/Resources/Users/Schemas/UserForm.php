<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal & Account Details')
                    ->description('Basic identification and role for this team member.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(50)
                            ->default(null),

                        Select::make('type')
                            ->label('User Type')
                            ->options(UserType::class)
                            ->default(UserType::SalesAgent)
                            ->live()
                            ->required(),

                        Select::make('roles')
                            ->label('Assigned Roles')
                            ->relationship('roles', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => str($record->name)->replace('_', ' ')->title()->toString())
                            ->preload()
                            ->searchable()
                            ->multiple()
                            ->required(function (Get $get): bool {
                                $type = $get('type');
                                $typeValue = $type instanceof UserType ? $type->value : (string) $type;

                                return $typeValue !== UserType::SuperAdmin->value;
                            })
                            ->helperText(function (Get $get): string {
                                $type = $get('type');
                                $typeValue = $type instanceof UserType ? $type->value : (string) $type;

                                return $typeValue === UserType::SuperAdmin->value
                                    ? 'SuperAdmin inherently has full access across the system (roles optional).'
                                    : 'At least one role is required for this user type.';
                            }),

                        FileUpload::make('image')
                            ->label('Profile Picture')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public'),

                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->helperText('Determines if the user can log into the dashboard.')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}

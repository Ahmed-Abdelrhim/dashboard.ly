<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Redirect to the users listing table after saving.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->visible(fn (): bool => auth()->user()?->can('view_users') ?? false),
            DeleteAction::make()
                ->visible(function (?User $record): bool {
                    /** @var User|null $authUser */
                    $authUser = auth()->user();

                    if (! $authUser || ! $authUser->can('delete_users')) {
                        return false;
                    }

                    return $record ? $record->getKey() !== $authUser->getKey() : true;
                }),
        ];
    }
}

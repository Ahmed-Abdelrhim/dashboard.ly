<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn (Role $record): bool => in_array($record->name, ['super_admin', 'admin', 'sales_agent'], true))
                ->visible(fn (): bool => auth()->user()?->can('delete_roles') ?? false),
        ];
    }

    /**
     * Redirect to the roles listing table after saving.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Ensure the super_admin role cannot be edited even via direct URL.
     */
    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->getRecord()->name === 'super_admin') {
            abort(403, 'The super_admin role cannot be edited.');
        }
    }
}

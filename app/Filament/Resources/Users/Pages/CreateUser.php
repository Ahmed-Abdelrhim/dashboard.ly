<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Mail\NewUserWelcomeMail;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Stored plain-text password for sending via welcome email.
     */
    protected ?string $plainPassword = null;

    /**
     * Intercept form data before creating to capture or generate plain password.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->plainPassword = isset($data['password']) && is_string($data['password']) && filled($data['password'])
            ? $data['password']
            : Str::password(length: 12, letters: true, numbers: true, symbols: false, spaces: false);

        $data['password'] = $this->plainPassword;

        return $data;
    }

    /**
     * Post-creation hook: queue welcome email containing credentials and dashboard URL in background.
     */
    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->record;

        if ($this->plainPassword) {
            try {
                Mail::to($user->email)->queue(new NewUserWelcomeMail($user, $this->plainPassword));

                Notification::make()
                    ->title('User created successfully')
                    ->body("Welcome email with login credentials has been sent to {$user->email}.")
                    ->success()
                    ->send();
            } catch (Throwable $e) {
                Log::error('Failed to queue new user welcome email: '.$e->getMessage(), [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'exception' => $e,
                ]);

                Notification::make()
                    ->title('User created, but welcome email failed to queue')
                    ->body('Please check your mail server configuration or provide the password manually.')
                    ->warning()
                    ->send();
            }
        }
    }

    /**
     * Redirect to the users list after creation.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

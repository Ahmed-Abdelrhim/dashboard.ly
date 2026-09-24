<?php

namespace App\Models;

use App\Enums\UserType;
use Database\Factories\UserFactory;
use DateTimeInterface;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $image
 * @property string|null $phone
 * @property string $password
 * @property UserType $type
 * @property bool $is_active
 * @property bool $2fa_enabled
 * @property string|null $2fa_secret
 * @property Carbon|null $2fa_confirmed_at
 * @property Carbon|null $last_login_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read bool $two_factor_enabled
 * @property-read Collection<int, UserSession> $sessions
 * @property-read int|null $sessions_count
 * @property-read Collection<int, UserSession> $activeSessions
 * @property-read int|null $active_sessions_count
 * @property-read UserSession|null $latestSession
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User active()
 * @method static Builder<static>|User inactive()
 * @method static Builder<static>|User ofType(\App\Enums\UserType|string $type)
 * @method static Builder<static>|User superAdmins()
 * @method static Builder<static>|User admins()
 * @method static Builder<static>|User salesAgents()
 * @method static Builder<static>|User twoFactorEnabled()
 */
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'image',
        'phone',
        'password',
        'type',
        'is_active',
        '2fa_enabled',
        '2fa_secret',
        '2fa_confirmed_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        '2fa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'type' => UserType::class,
            'is_active' => 'boolean',
            '2fa_enabled' => 'boolean',
            '2fa_secret' => 'encrypted',
            '2fa_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Sessions for this user.
     *
     * @return HasMany<UserSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class, 'user_id', 'id');
    }

    /**
     * Currently active sessions for this user.
     *
     * @return HasMany<UserSession, $this>
     */
    public function activeSessions(): HasMany
    {
        return $this->sessions()->where('is_active', true)->whereNull('revoked_at');
    }

    /**
     * Most recent session for this user.
     *
     * @return HasOne<UserSession, $this>
     */
    public function latestSession(): HasOne
    {
        return $this->hasOne(UserSession::class, 'user_id', 'id')->latestOfMany('login_at');
    }

    /**
     * Leads assigned to this user.
     *
     * @return HasMany<Lead, $this>
     */
    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to', 'id');
    }

    /**
     * Scope a query to only include active users.
     *
     * @param  Builder<User>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive users.
     *
     * @param  Builder<User>  $query
     */
    public function scopeInactive(Builder $query): void
    {
        $query->where('is_active', false);
    }

    /**
     * Scope a query to filter by user type.
     *
     * @param  Builder<User>  $query
     */
    public function scopeOfType(Builder $query, UserType|string $type): void
    {
        $value = $type instanceof UserType ? $type->value : $type;
        $query->where('type', $value);
    }

    /**
     * Scope a query to only include super admins.
     *
     * @param  Builder<User>  $query
     */
    public function scopeSuperAdmins(Builder $query): void
    {
        $query->where('type', UserType::SuperAdmin->value);
    }

    /**
     * Scope a query to only include admins.
     *
     * @param  Builder<User>  $query
     */
    public function scopeAdmins(Builder $query): void
    {
        $query->where('type', UserType::Admin->value);
    }

    /**
     * Scope a query to only include sales agents.
     *
     * @param  Builder<User>  $query
     */
    public function scopeSalesAgents(Builder $query): void
    {
        $query->where('type', UserType::SalesAgent->value);
    }

    /**
     * Scope a query to only include users with 2FA enabled.
     *
     * @param  Builder<User>  $query
     */
    public function scopeTwoFactorEnabled(Builder $query): void
    {
        $query->where('2fa_enabled', true);
    }

    /**
     * Check whether the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->type === UserType::SuperAdmin;
    }

    /**
     * Check whether the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->type === UserType::Admin;
    }

    /**
     * Check whether the user is a sales agent.
     */
    public function isSalesAgent(): bool
    {
        return $this->type === UserType::SalesAgent;
    }

    /**
     * Check whether the user account is active.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Determine if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isActive();
    }

    /**
     * Get the user's avatar URL for Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    /**
     * Get the user's avatar URL or fall back to Filament default initials avatar.
     */
    public function getAvatarUrl(): string
    {
        return filament()->getUserAvatarUrl($this);
    }

    /**
     * Check whether 2FA is enabled for this user.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return (bool) $this->getAttribute('2fa_enabled');
    }

    /**
     * Check whether 2FA has been confirmed for this user.
     */
    public function hasConfirmedTwoFactor(): bool
    {
        return $this->hasTwoFactorEnabled() && $this->getAttribute('2fa_confirmed_at') !== null;
    }

    /**
     * Enable 2FA with a secret.
     */
    public function enableTwoFactor(string $secret): void
    {
        $this->forceFill([
            '2fa_enabled' => true,
            '2fa_secret' => $secret,
            '2fa_confirmed_at' => null,
        ])->save();
    }

    /**
     * Confirm 2FA setup.
     */
    public function confirmTwoFactor(): void
    {
        $this->forceFill([
            '2fa_confirmed_at' => now(),
        ])->save();
    }

    /**
     * Disable 2FA.
     */
    public function disableTwoFactor(): void
    {
        $this->forceFill([
            '2fa_enabled' => false,
            '2fa_secret' => null,
            '2fa_confirmed_at' => null,
        ])->save();
    }

    /**
     * Record a login timestamp.
     */
    public function recordLogin(?DateTimeInterface $loginAt = null): void
    {
        $this->forceFill([
            'last_login_at' => $loginAt ?? now(),
        ])->save();
    }

    /**
     * Revoke all active sessions for this user.
     */
    public function revokeAllSessions(): int
    {
        return $this->sessions()
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'revoked_at' => now(),
            ]);
    }

    /**
     * Determine if two-factor authentication is enabled.
     */
    public function getTwoFactorEnabledAttribute(): bool
    {
        return $this->hasTwoFactorEnabled();
    }
}

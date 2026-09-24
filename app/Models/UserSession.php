<?php

namespace App\Models;

use App\Enums\AuthenticationType;
use Database\Factories\UserSessionFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * App\Models\UserSession
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $sanctum_token_id
 * @property string $session_identifier
 * @property AuthenticationType $authentication_type
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $device_name
 * @property string|null $device_type
 * @property string|null $os_name
 * @property string|null $os_version
 * @property string|null $browser_name
 * @property string|null $browser_version
 * @property string|null $country
 * @property string|null $country_code
 * @property string|null $city
 * @property Carbon $login_at
 * @property Carbon|null $last_activity_at
 * @property Carbon|null $logout_at
 * @property string|null $last_activity_ip
 * @property bool $is_active
 * @property Carbon|null $revoked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read PersonalAccessToken|null $sanctumToken
 *
 * @method static \Database\Factories\UserSessionFactory factory($count = null, $state = [])
 * @method static Builder<static>|UserSession newModelQuery()
 * @method static Builder<static>|UserSession newQuery()
 * @method static Builder<static>|UserSession query()
 * @method static Builder<static>|UserSession active()
 * @method static Builder<static>|UserSession inactive()
 * @method static Builder<static>|UserSession revoked()
 * @method static Builder<static>|UserSession bearerTokens()
 * @method static Builder<static>|UserSession sessionCookies()
 * @method static Builder<static>|UserSession forUser(\App\Models\User|int $user)
 * @method static Builder<static>|UserSession recentlyActive(int $minutes = 120)
 */
class UserSession extends Model
{
    /** @use HasFactory<UserSessionFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'sanctum_token_id',
        'session_identifier',
        'authentication_type',
        'ip_address',
        'user_agent',
        'device_name',
        'device_type',
        'os_name',
        'os_version',
        'browser_name',
        'browser_version',
        'country',
        'country_code',
        'city',
        'login_at',
        'last_activity_at',
        'logout_at',
        'last_activity_ip',
        'is_active',
        'revoked_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'authentication_type' => AuthenticationType::class,
            'login_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'logout_at' => 'datetime',
            'revoked_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the session.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the Sanctum personal access token associated with this session.
     *
     * @return BelongsTo<PersonalAccessToken, $this>
     */
    public function sanctumToken(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class, 'sanctum_token_id', 'id');
    }

    /**
     * Scope a query to only include active sessions.
     *
     * @param  Builder<UserSession>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->whereNull('revoked_at');
    }

    /**
     * Scope a query to only include inactive sessions.
     *
     * @param  Builder<UserSession>  $query
     */
    public function scopeInactive(Builder $query): void
    {
        $query->where('is_active', false);
    }

    /**
     * Scope a query to only include revoked sessions.
     *
     * @param  Builder<UserSession>  $query
     */
    public function scopeRevoked(Builder $query): void
    {
        $query->whereNotNull('revoked_at');
    }

    /**
     * Scope a query to only include bearer token sessions.
     *
     * @param  Builder<UserSession>  $query
     */
    public function scopeBearerTokens(Builder $query): void
    {
        $query->where('authentication_type', AuthenticationType::BearerToken->value);
    }

    /**
     * Scope a query to only include session cookie sessions.
     *
     * @param  Builder<UserSession>  $query
     */
    public function scopeSessionCookies(Builder $query): void
    {
        $query->where('authentication_type', AuthenticationType::SessionCookie->value);
    }

    /**
     * Scope a query to filter by user.
     *
     * @param  Builder<UserSession>  $query
     */
    public function scopeForUser(Builder $query, User|int $user): void
    {
        $userId = $user instanceof User ? $user->id : $user;
        $query->where('user_id', $userId);
    }

    /**
     * Scope a query to sessions active within the given number of minutes.
     *
     * @param  Builder<UserSession>  $query
     */
    public function scopeRecentlyActive(Builder $query, int $minutes = 120): void
    {
        $query->where('last_activity_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Revoke this session.
     */
    public function revoke(?DateTimeInterface $revokedAt = null): bool
    {
        $timestamp = $revokedAt ?? now();

        return $this->update([
            'is_active' => false,
            'revoked_at' => $timestamp,
            'logout_at' => $this->logout_at ?? $timestamp,
        ]);
    }

    /**
     * Mark the session as logged out.
     */
    public function markLoggedOut(?DateTimeInterface $logoutAt = null): bool
    {
        $timestamp = $logoutAt ?? now();

        return $this->update([
            'is_active' => false,
            'logout_at' => $timestamp,
            'revoked_at' => $this->revoked_at ?? $timestamp,
        ]);
    }

    /**
     * Record new activity for this session.
     */
    public function recordActivity(?string $ipAddress = null): bool
    {
        $attributes = [
            'last_activity_at' => now(),
        ];

        if ($ipAddress !== null) {
            $attributes['last_activity_ip'] = $ipAddress;
        }

        return $this->update($attributes);
    }

    /**
     * Check if this session was authenticated via Bearer Token.
     */
    public function isBearerToken(): bool
    {
        return $this->authentication_type === AuthenticationType::BearerToken;
    }

    /**
     * Check if this session was authenticated via Session Cookie.
     */
    public function isSessionCookie(): bool
    {
        return $this->authentication_type === AuthenticationType::SessionCookie;
    }

    /**
     * Check if this session has been revoked.
     */
    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    /**
     * Check if this session is active.
     */
    public function isActive(): bool
    {
        return $this->is_active && ! $this->isRevoked();
    }

    /**
     * Get a human-friendly device summary string.
     */
    public function deviceSummary(): string
    {
        $parts = array_filter([
            $this->browser_name,
            $this->os_name,
            $this->device_name,
        ]);

        return ! empty($parts) ? implode(' on ', $parts) : ($this->user_agent ?: 'Unknown Device');
    }

    /**
     * Get a human-friendly location summary string.
     */
    public function locationSummary(): string
    {
        $parts = array_filter([
            $this->city,
            $this->country,
        ]);

        return ! empty($parts) ? implode(', ', $parts) : 'Unknown Location';
    }
}

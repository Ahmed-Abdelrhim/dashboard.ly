<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * App\Models\Lead
 *
 * @property int $id
 * @property string $lead_number
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property int|null $age
 * @property int|null $wavex_user_id
 * @property int|null $country_id
 * @property int|null $branch_id
 * @property int|null $campaign_id
 * @property bool|null $tried_aqua_fitness
 * @property array<string>|null $interested_in
 * @property string|null $sessions_considering
 * @property array<string>|null $preferred_days
 * @property array<string>|null $preferred_times
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property string|null $utm_campaign
 * @property string|null $utm_term
 * @property string|null $utm_content
 * @property string|null $fbclid
 * @property string|null $gclid
 * @property string|null $ttclid
 * @property string|null $landing_page_url
 * @property string|null $referrer_url
 * @property int|null $assigned_to
 * @property LeadStatus $status
 * @property Carbon|null $first_contacted_at
 * @property Carbon|null $last_contacted_at
 * @property Carbon|null $won_at
 * @property Carbon|null $lost_at
 * @property string|null $lost_reason
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Campaign|null $campaign
 * @property-read User|null $assignedUser
 *
 * @method static \Database\Factories\LeadFactory factory($count = null, $state = [])
 * @method static Builder<static>|Lead newModelQuery()
 * @method static Builder<static>|Lead newQuery()
 * @method static Builder<static>|Lead query()
 * @method static Builder<static>|Lead status(\App\Enums\LeadStatus|string $status)
 * @method static Builder<static>|Lead assignedTo(\App\Models\User|int $user)
 * @method static Builder<static>|Lead unassigned()
 */
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'leads';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'lead_number',
        'name',
        'email',
        'phone',
        'age',
        'wavex_user_id',
        'country_id',
        'branch_id',
        'campaign_id',
        'tried_aqua_fitness',
        'interested_in',
        'sessions_considering',
        'preferred_days',
        'preferred_times',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'fbclid',
        'gclid',
        'ttclid',
        'landing_page_url',
        'referrer_url',
        'assigned_to',
        'status',
        'first_contacted_at',
        'last_contacted_at',
        'won_at',
        'lost_at',
        'lost_reason',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'wavex_user_id' => 'integer',
            'country_id' => 'integer',
            'branch_id' => 'integer',
            'campaign_id' => 'integer',
            'assigned_to' => 'integer',
            'tried_aqua_fitness' => 'boolean',
            'interested_in' => 'array',
            'preferred_days' => 'array',
            'preferred_times' => 'array',
            'status' => LeadStatus::class,
            'first_contacted_at' => 'datetime',
            'last_contacted_at' => 'datetime',
            'won_at' => 'datetime',
            'lost_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Lead $lead): void {
            if (empty($lead->lead_number)) {
                $lead->lead_number = self::generateLeadNumber();
            }
        });
    }

    /**
     * Generate a unique lead number.
     */
    public static function generateLeadNumber(): string
    {
        return 'LD-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
    }

    /**
     * Get the campaign associated with the lead.
     *
     * @return BelongsTo<Campaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    /**
     * Get the user assigned to this lead.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    /**
     * Scope a query to filter by lead status.
     *
     * @param  Builder<Lead>  $query
     */
    public function scopeStatus(Builder $query, LeadStatus|string $status): void
    {
        $statusValue = $status instanceof LeadStatus ? $status->value : $status;
        $query->where('status', $statusValue);
    }

    /**
     * Scope a query to filter by assigned user.
     *
     * @param  Builder<Lead>  $query
     */
    public function scopeAssignedTo(Builder $query, User|int $user): void
    {
        $userId = $user instanceof User ? $user->id : $user;
        $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to only include unassigned leads.
     *
     * @param  Builder<Lead>  $query
     */
    public function scopeUnassigned(Builder $query): void
    {
        $query->whereNull('assigned_to');
    }
}

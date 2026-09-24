<?php

namespace App\Models;

use Database\Factories\CampaignFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Campaign
 *
 * @property int $id
 * @property string $name
 * @property string|null $platform
 * @property string|null $utm_campaign
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Lead> $leads
 * @property-read int|null $leads_count
 *
 * @method static \Database\Factories\CampaignFactory factory($count = null, $state = [])
 * @method static Builder<static>|Campaign newModelQuery()
 * @method static Builder<static>|Campaign newQuery()
 * @method static Builder<static>|Campaign query()
 * @method static Builder<static>|Campaign active()
 * @method static Builder<static>|Campaign platform(string $platform)
 */
class Campaign extends Model
{
    /** @use HasFactory<CampaignFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'campaigns';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'platform',
        'utm_campaign',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the leads associated with this campaign.
     *
     * @return HasMany<Lead, $this>
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'campaign_id', 'id');
    }

    /**
     * Scope a query to only include active campaigns.
     *
     * @param  Builder<Campaign>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to filter campaigns by platform.
     *
     * @param  Builder<Campaign>  $query
     */
    public function scopePlatform(Builder $query, string $platform): void
    {
        $query->where('platform', $platform);
    }
}

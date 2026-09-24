<?php

namespace App\Http\Resources;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Lead
 */
class LeadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_number' => $this->lead_number,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'age' => $this->age,
            'wavex_user_id' => $this->wavex_user_id,
            'country_id' => $this->country_id,
            'branch_id' => $this->branch_id,
            'campaign_id' => $this->campaign_id,
            'campaign' => $this->whenLoaded('campaign', function () {
                return [
                    'id' => $this->campaign?->id,
                    'name' => $this->campaign?->name,
                    'platform' => $this->campaign?->platform,
                ];
            }),

            'form' => [
                'tried_aqua_fitness' => $this->tried_aqua_fitness,
                'interested_in' => $this->interested_in,
                'sessions_considering' => $this->sessions_considering,
                'preferred_days' => $this->preferred_days,
                'preferred_times' => $this->preferred_times,
            ],

            'attribution' => [
                'utm_source' => $this->utm_source,
                'utm_medium' => $this->utm_medium,
                'utm_campaign' => $this->utm_campaign,
                'utm_term' => $this->utm_term,
                'utm_content' => $this->utm_content,
                'fbclid' => $this->fbclid,
                'gclid' => $this->gclid,
                'ttclid' => $this->ttclid,
                'landing_page_url' => $this->landing_page_url,
                'referrer_url' => $this->referrer_url,
            ],

            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

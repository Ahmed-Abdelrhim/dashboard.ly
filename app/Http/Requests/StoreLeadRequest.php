<?php

namespace App\Http\Requests;

use App\Models\Campaign;
use App\Rules\ValidateEgyptianOrUkPhone;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-decode JSON strings if sent via multipart/form-data or urlencoded form
        $arrayFields = ['interested_in', 'preferred_days', 'preferred_times'];
        $decodedFields = [];

        foreach ($arrayFields as $field) {
            $value = $this->input($field);
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $decodedFields[$field] = $decoded;
                }
            }
        }

        // If landing_page_url or referrer_url are not provided, auto-capture from HTTP headers
        $defaults = [];
        if (! $this->filled('referrer_url') && $this->headers->has('referer')) {
            $defaults['referrer_url'] = $this->headers->get('referer');
        }

        // If campaign_id is not given, attempt to resolve from utm_campaign
        if (! $this->filled('campaign_id') && $this->filled('utm_campaign')) {
            $utmCampaign = (string) $this->input('utm_campaign');
            $matchedCampaign = Campaign::query()
                ->where('utm_campaign', $utmCampaign)
                ->orWhere('name', $utmCampaign)
                ->first();

            if ($matchedCampaign) {
                $defaults['campaign_id'] = $matchedCampaign->id;
            }
        }

        // Normalize phone number by removing spaces, dashes, and parentheses
        if ($this->filled('phone')) {
            $defaults['phone'] = preg_replace('/[\s\-\(\)]/', '', (string) $this->input('phone'));
        }

        $mergeData = array_merge($decodedFields, $defaults);
        if (! empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Basic Contact Information
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'required_without:phone', 'string', 'email:rfc', 'max:255', 'unique:leads,email'],
            'phone' => ['required', 'required_without:email', 'string', 'max:50', new ValidateEgyptianOrUkPhone, 'unique:leads,phone'],
            'age' => ['nullable', 'integer', 'between:1,120'],

            // WAVEX Identifiers
            'wavex_user_id' => ['nullable', 'integer', 'min:1'],
            'country_id' => ['nullable', 'integer', 'min:1'],
            'branch_id' => ['nullable', 'integer', 'min:1'],

            // Marketing Source & Campaign
            'campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],

            // Form Data
            'tried_aqua_fitness' => ['nullable', 'boolean'],
            'interested_in' => ['nullable', 'array'],
            'interested_in.*' => ['string', 'max:100'],
            'sessions_considering' => ['nullable', 'string', 'max:50'],
            'preferred_days' => ['nullable', 'array'],
            'preferred_days.*' => ['string', 'max:50'],
            'preferred_times' => ['nullable', 'array'],
            'preferred_times.*' => ['string', 'max:50'],

            // Marketing Attribution (UTM Parameters)
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],

            // Ad Click Identifiers
            'fbclid' => ['nullable', 'string', 'max:255'],
            'gclid' => ['nullable', 'string', 'max:255'],
            'ttclid' => ['nullable', 'string', 'max:255'],

            // Attribution Context
            'landing_page_url' => ['nullable', 'string', 'url', 'max:2048'],
            'referrer_url' => ['nullable', 'string', 'url', 'max:2048'],

            // Additional Notes
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide your full name.',
            'email.required_without' => 'Please provide either an email address or a phone number.',
            'phone.required_without' => 'Please provide either a phone number or an email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'A lead with this email address already exists.',
            'phone.unique' => 'A lead with this phone number already exists.',
            'campaign_id.exists' => 'The selected marketing campaign is invalid or does not exist.',
            'landing_page_url.url' => 'The landing page URL must be a valid URL.',
            'referrer_url.url' => 'The referrer URL must be a valid URL.',
            'age.between' => 'The age must be between 1 and 120.',
        ];
    }
}

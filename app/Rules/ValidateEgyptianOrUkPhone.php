<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidateEgyptianOrUkPhone implements ValidationRule
{
    /**
     * Validate whether a phone number matches Egyptian or UK phone number patterns.
     */
    public function handle(?string $phone): bool
    {
        if (empty($phone)) {
            return true;
        }

        // Remove all spaces, dashes, and parentheses
        $cleanPhone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Egyptian phone number patterns
        $egyptianPatterns = [
            // Mobile numbers with country code
            '/^\+20(10|11|12|15)[0-9]{8}$/',
            '/^0020(10|11|12|15)[0-9]{8}$/',

            // Mobile numbers without country code
            '/^(010|011|012|015)[0-9]{8}$/',

            // Landline numbers with country code
            '/^\+20[23-9][0-9]{7,8}$/',
            '/^0020[23-9][0-9]{7,8}$/',

            // Landline numbers without country code
            '/^0[23-9][0-9]{7,8}$/',
        ];

        // UK phone number patterns
        $ukPatterns = [
            // Mobile numbers with country code
            '/^\+447[0-9]{9}$/',
            '/^00447[0-9]{9}$/',

            // Mobile numbers without country code
            '/^07[0-9]{9}$/',

            // Landline numbers with country code
            '/^\+44(1[0-9]{2}|20|23|24|28|29)[0-9]{7,8}$/',
            '/^0044(1[0-9]{2}|20|23|24|28|29)[0-9]{7,8}$/',

            // Landline numbers without country code
            '/^0(1[0-9]{2}|20|23|24|28|29)[0-9]{7,8}$/',

            // Special UK numbers
            '/^\+44800[0-9]{6}$/',
            '/^0800[0-9]{6}$/',
        ];

        // Check against Egyptian patterns
        foreach ($egyptianPatterns as $pattern) {
            if (preg_match($pattern, $cleanPhone)) {
                return true;
            }
        }

        // Check against UK patterns
        foreach ($ukPatterns as $pattern) {
            if (preg_match($pattern, $cleanPhone)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! $this->handle($value)) {
            $fail('The :attribute must be a valid Egyptian or UK phone number.');
        }
    }
}

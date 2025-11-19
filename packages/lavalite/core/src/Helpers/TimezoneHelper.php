<?php

namespace Lavalite\Core\Helpers;

use Carbon\Carbon;
use DateTimeZone;

class TimezoneHelper
{
    /**
     * Common timezones with their display names.
     */
    protected static array $commonTimezones = [
        'UTC' => 'UTC',
        'America/New_York' => 'Eastern Time (US & Canada)',
        'America/Chicago' => 'Central Time (US & Canada)',
        'America/Denver' => 'Mountain Time (US & Canada)',
        'America/Los_Angeles' => 'Pacific Time (US & Canada)',
        'America/Anchorage' => 'Alaska',
        'Pacific/Honolulu' => 'Hawaii',
        'America/Toronto' => 'Eastern Time (Canada)',
        'America/Vancouver' => 'Pacific Time (Canada)',
        'America/Mexico_City' => 'Mexico City',
        'America/Sao_Paulo' => 'Brasilia',
        'America/Argentina/Buenos_Aires' => 'Buenos Aires',
        'Europe/London' => 'London',
        'Europe/Paris' => 'Paris',
        'Europe/Berlin' => 'Berlin',
        'Europe/Rome' => 'Rome',
        'Europe/Madrid' => 'Madrid',
        'Europe/Amsterdam' => 'Amsterdam',
        'Europe/Brussels' => 'Brussels',
        'Europe/Vienna' => 'Vienna',
        'Europe/Stockholm' => 'Stockholm',
        'Europe/Warsaw' => 'Warsaw',
        'Europe/Athens' => 'Athens',
        'Europe/Istanbul' => 'Istanbul',
        'Europe/Moscow' => 'Moscow',
        'Asia/Dubai' => 'Dubai',
        'Asia/Karachi' => 'Karachi',
        'Asia/Kolkata' => 'Mumbai, Kolkata, New Delhi',
        'Asia/Bangkok' => 'Bangkok',
        'Asia/Singapore' => 'Singapore',
        'Asia/Hong_Kong' => 'Hong Kong',
        'Asia/Shanghai' => 'Beijing, Shanghai',
        'Asia/Tokyo' => 'Tokyo',
        'Asia/Seoul' => 'Seoul',
        'Australia/Sydney' => 'Sydney',
        'Australia/Melbourne' => 'Melbourne',
        'Australia/Perth' => 'Perth',
        'Pacific/Auckland' => 'Auckland',
        'Africa/Cairo' => 'Cairo',
        'Africa/Johannesburg' => 'Johannesburg',
    ];

    /**
     * Convert datetime to organization timezone.
     */
    public static function toOrganization(Carbon|string $datetime, ?string $timezone = null): Carbon
    {
        $timezone = $timezone ?? static::getOrganizationTimezone();

        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return $datetime->timezone($timezone);
    }

    /**
     * Convert datetime from organization timezone to UTC.
     */
    public static function toUTC(Carbon|string $datetime, ?string $timezone = null): Carbon
    {
        $timezone = $timezone ?? static::getOrganizationTimezone();

        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime, $timezone);
        }

        return $datetime->timezone('UTC');
    }

    /**
     * Format datetime in organization timezone.
     */
    public static function format(Carbon|string $datetime, string $format = 'Y-m-d H:i:s', ?string $timezone = null): string
    {
        return static::toOrganization($datetime, $timezone)->format($format);
    }

    /**
     * Get human-readable time difference (e.g., "2 hours ago").
     */
    public static function diffForHumans(Carbon|string $datetime, ?string $timezone = null): string
    {
        return static::toOrganization($datetime, $timezone)->diffForHumans();
    }

    /**
     * Get current time in organization timezone.
     */
    public static function now(?string $timezone = null): Carbon
    {
        $timezone = $timezone ?? static::getOrganizationTimezone();

        return Carbon::now($timezone);
    }

    /**
     * Get today's date in organization timezone.
     */
    public static function today(?string $timezone = null): Carbon
    {
        $timezone = $timezone ?? static::getOrganizationTimezone();

        return Carbon::today($timezone);
    }

    /**
     * Get all available timezones.
     */
    public static function all(): array
    {
        return collect(DateTimeZone::listIdentifiers())
            ->mapWithKeys(fn ($tz) => [$tz => static::$commonTimezones[$tz] ?? $tz])
            ->toArray();
    }

    /**
     * Get common timezones (curated list).
     */
    public static function common(): array
    {
        return static::$commonTimezones;
    }

    /**
     * Get timezone offset in hours.
     */
    public static function offset(?string $timezone = null): string
    {
        $timezone = $timezone ?? static::getOrganizationTimezone();
        $dt = new \DateTime('now', new DateTimeZone($timezone));
        $offset = $dt->getOffset();

        $hours = abs($offset) / 3600;
        $minutes = abs($offset) % 3600 / 60;

        $sign = $offset >= 0 ? '+' : '-';

        return sprintf('%s%02d:%02d', $sign, $hours, $minutes);
    }

    /**
     * Get timezone abbreviation (e.g., EST, PST).
     */
    public static function abbreviation(?string $timezone = null): string
    {
        $timezone = $timezone ?? static::getOrganizationTimezone();
        $dt = new \DateTime('now', new DateTimeZone($timezone));

        return $dt->format('T');
    }

    /**
     * Check if timezone is valid.
     */
    public static function isValid(string $timezone): bool
    {
        return in_array($timezone, DateTimeZone::listIdentifiers());
    }

    /**
     * Get business hours for organization.
     */
    public static function getBusinessHours(?string $timezone = null): array
    {
        $timezone = $timezone ?? static::getOrganizationTimezone();

        return [
            'start' => Carbon::parse('09:00', $timezone),
            'end' => Carbon::parse('17:00', $timezone),
            'timezone' => $timezone,
        ];
    }

    /**
     * Check if current time is within business hours.
     */
    public static function isBusinessHours(?string $timezone = null): bool
    {
        $hours = static::getBusinessHours($timezone);
        $now = static::now($timezone);

        return $now->between($hours['start'], $hours['end']) && ! $now->isWeekend();
    }

    /**
     * Get timezone from current organization context.
     */
    protected static function getOrganizationTimezone(): string
    {
        $organization = app('organization');

        return $organization?->timezone ?? config('app.timezone', 'UTC');
    }

    /**
     * Format date range.
     */
    public static function formatDateRange(Carbon|string $start, Carbon|string $end, string $format = 'Y-m-d', ?string $timezone = null): string
    {
        $start = static::toOrganization($start, $timezone);
        $end = static::toOrganization($end, $timezone);

        if ($start->isSameDay($end)) {
            return $start->format($format);
        }

        return $start->format($format).' - '.$end->format($format);
    }
}

<?php

if (! function_exists('to_user_timezone')) {
    /**
     * Convert a datetime to the user's timezone
     *
     * @param  string|\DateTimeInterface  $datetime
     */
    function to_user_timezone($datetime, ?string $timezone = null, string $format = 'Y-m-d H:i:s'): string
    {
        if (! $datetime) {
            return '';
        }

        $timezone = $timezone ?? auth()->user()?->timezone ?? config('app.timezone', 'UTC');

        return \Carbon\Carbon::parse($datetime)
            ->setTimezone($timezone)
            ->format($format);
    }
}

if (! function_exists('to_utc')) {
    /**
     * Convert a datetime to UTC
     *
     * @param  string|\DateTimeInterface  $datetime
     */
    function to_utc($datetime, ?string $fromTimezone = null): \Carbon\Carbon
    {
        if (! $datetime) {
            return \Carbon\Carbon::now('UTC');
        }

        $fromTimezone = $fromTimezone ?? auth()->user()?->timezone ?? config('app.timezone', 'UTC');

        return \Carbon\Carbon::parse($datetime, $fromTimezone)->setTimezone('UTC');
    }
}

if (! function_exists('format_datetime')) {
    /**
     * Format a datetime in user's timezone and locale
     *
     * @param  string|\DateTimeInterface  $datetime
     * @param  string  $format  Options: 'short', 'medium', 'long', 'full', or custom format
     */
    function format_datetime($datetime, string $format = 'medium', ?string $timezone = null): string
    {
        if (! $datetime) {
            return '';
        }

        $timezone = $timezone ?? auth()->user()?->timezone ?? config('app.timezone', 'UTC');
        $carbon = \Carbon\Carbon::parse($datetime)->setTimezone($timezone);

        return match ($format) {
            'short' => $carbon->format('Y-m-d H:i'),
            'medium' => $carbon->format('M d, Y H:i'),
            'long' => $carbon->format('F d, Y g:i A'),
            'full' => $carbon->format('l, F d, Y g:i:s A T'),
            'date_only' => $carbon->format('Y-m-d'),
            'time_only' => $carbon->format('H:i:s'),
            'human' => $carbon->diffForHumans(),
            default => $carbon->format($format),
        };
    }
}

if (! function_exists('current_timezone')) {
    /**
     * Get the current user/organization timezone
     */
    function current_timezone(): string
    {
        return config('app.timezone', 'UTC');
    }
}

if (! function_exists('list_timezones')) {
    /**
     * Get list of common timezones grouped by region
     */
    function list_timezones(): array
    {
        $regions = [
            'Africa' => \DateTimeZone::AFRICA,
            'America' => \DateTimeZone::AMERICA,
            'Antarctica' => \DateTimeZone::ANTARCTICA,
            'Arctic' => \DateTimeZone::ARCTIC,
            'Asia' => \DateTimeZone::ASIA,
            'Atlantic' => \DateTimeZone::ATLANTIC,
            'Australia' => \DateTimeZone::AUSTRALIA,
            'Europe' => \DateTimeZone::EUROPE,
            'Indian' => \DateTimeZone::INDIAN,
            'Pacific' => \DateTimeZone::PACIFIC,
        ];

        $timezones = [];
        foreach ($regions as $region => $identifier) {
            $timezones[$region] = \DateTimeZone::listIdentifiers($identifier);
        }

        return $timezones;
    }
}

if (! function_exists('timezone_offset')) {
    /**
     * Get timezone offset in hours
     */
    function timezone_offset(?string $timezone = null): string
    {
        $timezone = $timezone ?? current_timezone();
        $tz = new \DateTimeZone($timezone);
        $offset = $tz->getOffset(new \DateTime('now', $tz));

        $hours = abs($offset) / 3600;
        $minutes = (abs($offset) % 3600) / 60;

        $sign = $offset >= 0 ? '+' : '-';

        return sprintf('%s%02d:%02d', $sign, $hours, $minutes);
    }
}

if (! function_exists('is_valid_timezone')) {
    /**
     * Check if a timezone is valid
     */
    function is_valid_timezone(string $timezone): bool
    {
        return in_array($timezone, \DateTimeZone::listIdentifiers());
    }
}

if (! function_exists('convert_timezone')) {
    /**
     * Convert datetime from one timezone to another
     *
     * @param  string|\DateTimeInterface  $datetime
     */
    function convert_timezone($datetime, string $fromTimezone, string $toTimezone): \Carbon\Carbon
    {
        return \Carbon\Carbon::parse($datetime, $fromTimezone)->setTimezone($toTimezone);
    }
}

if (! function_exists('get_popular_timezones')) {
    /**
     * Get list of most commonly used timezones
     */
    function get_popular_timezones(): array
    {
        return [
            'UTC' => 'UTC (Coordinated Universal Time)',
            'America/New_York' => 'Eastern Time (US & Canada)',
            'America/Chicago' => 'Central Time (US & Canada)',
            'America/Denver' => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'America/Anchorage' => 'Alaska',
            'Pacific/Honolulu' => 'Hawaii',
            'Europe/London' => 'London (GMT)',
            'Europe/Paris' => 'Paris, Brussels, Amsterdam',
            'Europe/Berlin' => 'Berlin, Rome, Stockholm',
            'Europe/Istanbul' => 'Istanbul, Athens, Bucharest',
            'Europe/Moscow' => 'Moscow, St. Petersburg',
            'Asia/Dubai' => 'Dubai, Abu Dhabi',
            'Asia/Karachi' => 'Karachi, Islamabad',
            'Asia/Kolkata' => 'Mumbai, Kolkata, New Delhi',
            'Asia/Dhaka' => 'Dhaka, Bangladesh',
            'Asia/Bangkok' => 'Bangkok, Hanoi, Jakarta',
            'Asia/Singapore' => 'Singapore, Kuala Lumpur',
            'Asia/Hong_Kong' => 'Hong Kong, Beijing, Shanghai',
            'Asia/Tokyo' => 'Tokyo, Osaka, Sapporo',
            'Asia/Seoul' => 'Seoul, South Korea',
            'Australia/Sydney' => 'Sydney, Melbourne',
            'Australia/Perth' => 'Perth',
            'Pacific/Auckland' => 'Auckland, Wellington',
            'Africa/Cairo' => 'Cairo, Egypt',
            'Africa/Johannesburg' => 'Johannesburg, Pretoria',
            'Africa/Lagos' => 'Lagos, Nigeria',
            'Africa/Nairobi' => 'Nairobi, Kenya',
        ];
    }
}

<?php

namespace Lavalite\Core\Helpers;

use Carbon\Carbon;

class DateTimeHelper
{
    /**
     * Common date formats.
     */
    public const FORMAT_DATABASE = 'Y-m-d H:i:s';

    public const FORMAT_DATE = 'Y-m-d';

    public const FORMAT_TIME = 'H:i:s';

    public const FORMAT_US = 'm/d/Y';

    public const FORMAT_EU = 'd/m/Y';

    public const FORMAT_ISO = 'c';

    public const FORMAT_RFC = 'r';

    public const FORMAT_HUMAN = 'F j, Y, g:i a';

    public const FORMAT_SHORT = 'M j, Y';

    /**
     * Format datetime for display.
     */
    public static function format(Carbon|string $datetime, ?string $format = null, ?string $timezone = null): string
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        if ($timezone) {
            $datetime = $datetime->timezone($timezone);
        }

        $format = $format ?? static::getOrganizationFormat();

        return $datetime->format($format);
    }

    /**
     * Parse datetime string to Carbon instance.
     */
    public static function parse(string $datetime, ?string $timezone = null): Carbon
    {
        $timezone = $timezone ?? TimezoneHelper::now()->timezone->getName();

        return Carbon::parse($datetime, $timezone);
    }

    /**
     * Get current datetime.
     */
    public static function now(?string $timezone = null): Carbon
    {
        return TimezoneHelper::now($timezone);
    }

    /**
     * Get today's date.
     */
    public static function today(?string $timezone = null): Carbon
    {
        return TimezoneHelper::today($timezone);
    }

    /**
     * Get yesterday's date.
     */
    public static function yesterday(?string $timezone = null): Carbon
    {
        return TimezoneHelper::today($timezone)->subDay();
    }

    /**
     * Get tomorrow's date.
     */
    public static function tomorrow(?string $timezone = null): Carbon
    {
        return TimezoneHelper::today($timezone)->addDay();
    }

    /**
     * Format datetime for database storage (always UTC).
     */
    public static function forDatabase(Carbon|string $datetime): string
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return $datetime->timezone('UTC')->format(static::FORMAT_DATABASE);
    }

    /**
     * Format datetime for API output (ISO 8601).
     */
    public static function forApi(Carbon|string $datetime, ?string $timezone = null): string
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        if ($timezone) {
            $datetime = $datetime->timezone($timezone);
        }

        return $datetime->toIso8601String();
    }

    /**
     * Get human-readable time difference.
     */
    public static function ago(Carbon|string $datetime, ?string $timezone = null): string
    {
        return TimezoneHelper::diffForHumans($datetime, $timezone);
    }

    /**
     * Check if date is today.
     */
    public static function isToday(Carbon|string $datetime, ?string $timezone = null): bool
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return TimezoneHelper::toOrganization($datetime, $timezone)->isToday();
    }

    /**
     * Check if date is yesterday.
     */
    public static function isYesterday(Carbon|string $datetime, ?string $timezone = null): bool
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return TimezoneHelper::toOrganization($datetime, $timezone)->isYesterday();
    }

    /**
     * Check if date is tomorrow.
     */
    public static function isTomorrow(Carbon|string $datetime, ?string $timezone = null): bool
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return TimezoneHelper::toOrganization($datetime, $timezone)->isTomorrow();
    }

    /**
     * Check if datetime is in the past.
     */
    public static function isPast(Carbon|string $datetime): bool
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return $datetime->isPast();
    }

    /**
     * Check if datetime is in the future.
     */
    public static function isFuture(Carbon|string $datetime): bool
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return $datetime->isFuture();
    }

    /**
     * Get start of day.
     */
    public static function startOfDay(Carbon|string $datetime, ?string $timezone = null): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return TimezoneHelper::toOrganization($datetime, $timezone)->startOfDay();
    }

    /**
     * Get end of day.
     */
    public static function endOfDay(Carbon|string $datetime, ?string $timezone = null): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return TimezoneHelper::toOrganization($datetime, $timezone)->endOfDay();
    }

    /**
     * Get start of week.
     */
    public static function startOfWeek(Carbon|string $datetime, ?string $timezone = null): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return TimezoneHelper::toOrganization($datetime, $timezone)->startOfWeek();
    }

    /**
     * Get end of week.
     */
    public static function endOfWeek(Carbon|string $datetime, ?string $timezone = null): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return TimezoneHelper::toOrganization($datetime, $timezone)->endOfWeek();
    }

    /**
     * Get start of month.
     */
    public static function startOfMonth(Carbon|string $datetime, ?string $timezone = null): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return TimezoneHelper::toOrganization($datetime, $timezone)->startOfMonth();
    }

    /**
     * Get end of month.
     */
    public static function endOfMonth(Carbon|string $datetime, ?string $timezone = null): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return TimezoneHelper::toOrganization($datetime, $timezone)->endOfMonth();
    }

    /**
     * Get start of year.
     */
    public static function startOfYear(Carbon|string $datetime, ?string $timezone = null): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return TimezoneHelper::toOrganization($datetime, $timezone)->startOfYear();
    }

    /**
     * Get end of year.
     */
    public static function endOfYear(Carbon|string $datetime, ?string $timezone = null): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        return TimezoneHelper::toOrganization($datetime, $timezone)->endOfYear();
    }

    /**
     * Calculate duration between two datetimes.
     */
    public static function duration(Carbon|string $start, Carbon|string $end, string $unit = 'hours'): float|int
    {
        if (is_string($start)) {
            $start = Carbon::parse($start);
        }

        if (is_string($end)) {
            $end = Carbon::parse($end);
        }

        return match ($unit) {
            'seconds' => $start->diffInSeconds($end),
            'minutes' => $start->diffInMinutes($end),
            'hours' => $start->diffInHours($end),
            'days' => $start->diffInDays($end),
            'weeks' => $start->diffInWeeks($end),
            'months' => $start->diffInMonths($end),
            'years' => $start->diffInYears($end),
            default => $start->diffInHours($end),
        };
    }

    /**
     * Format duration in human-readable format.
     */
    public static function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours.' '.($hours === 1 ? 'hour' : 'hours');
        }

        if ($minutes > 0) {
            $parts[] = $minutes.' '.($minutes === 1 ? 'minute' : 'minutes');
        }

        if ($seconds > 0 || empty($parts)) {
            $parts[] = $seconds.' '.($seconds === 1 ? 'second' : 'seconds');
        }

        return implode(' ', $parts);
    }

    /**
     * Get organization date format from settings.
     */
    protected static function getOrganizationFormat(): string
    {
        $organization = app('organization');

        return $organization?->settings['date_format'] ?? static::FORMAT_HUMAN;
    }

    /**
     * Create date range.
     */
    public static function range(Carbon|string $start, Carbon|string $end, string $interval = '1 day'): array
    {
        if (is_string($start)) {
            $start = Carbon::parse($start);
        }

        if (is_string($end)) {
            $end = Carbon::parse($end);
        }

        $dates = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $dates[] = $current->copy();
            $current->add(\DateInterval::createFromDateString($interval));
        }

        return $dates;
    }

    /**
     * Check if two datetimes are on the same day.
     */
    public static function isSameDay(Carbon|string $date1, Carbon|string $date2, ?string $timezone = null): bool
    {
        if (is_string($date1)) {
            $date1 = Carbon::parse($date1);
        }

        if (is_string($date2)) {
            $date2 = Carbon::parse($date2);
        }

        $date1 = TimezoneHelper::toOrganization($date1, $timezone);
        $date2 = TimezoneHelper::toOrganization($date2, $timezone);

        return $date1->isSameDay($date2);
    }
}

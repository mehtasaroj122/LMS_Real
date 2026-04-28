<?php

namespace App\Helpers;

use Carbon\Carbon;
use NobelzSushank\Bsad\Facades\NepaliDateConverter;

/**
 * Date formatting helper for Bikram Sambat and Gregorian calendars.
 *
 * This helper provides centralized date conversion and formatting,
 * removing the need to maintain large calendar data structures in views.
 *
 * Uses the nobelzsushank/nepali-date-converter package which maintains
 * an up-to-date calendar dataset separate from the codebase.
 */
class DateHelper
{
    /**
     * Bikram Sambat month names.
     */
    private const BS_MONTHS = [
        'Baishakh',
        'Jestha',
        'Ashadh',
        'Shrawan',
        'Bhadra',
        'Ashwin',
        'Kartik',
        'Mangsir',
        'Poush',
        'Magh',
        'Falgun',
        'Chaitra',
    ];

    /**
     * Format a date as Bikram Sambat.
     *
     * Converts AD date to BS and formats as "Weekday, Month Day, Year"
     * Falls back to Gregorian format if conversion fails.
     *
     * @param  \Carbon\Carbon|string|null  $date
     * @return string
     */
    public static function formatAsBikramSambat($date = null): string
    {
        try {
            if (is_null($date)) {
                $date = now('Asia/Kathmandu');
            } elseif (is_string($date)) {
                $date = Carbon::parse($date, 'Asia/Kathmandu');
            } elseif (!$date instanceof Carbon) {
                $date = Carbon::instance($date, 'Asia/Kathmandu');
            }

            // Convert to Bikram Sambat using the package
            $bsDate = NepaliDateConverter::adToBs($date);

            $weekday = $date->format('l');
            $month = self::BS_MONTHS[$bsDate->month - 1] ?? 'Unknown';

            return "{$weekday}, {$month} {$bsDate->day}, {$bsDate->year}";
        } catch (\Exception $e) {
            // Fallback to Gregorian format if conversion fails
            return self::formatAsGregorian($date);
        }
    }

    /**
     * Format a date as Gregorian (long format).
     *
     * @param  \Carbon\Carbon|string|null  $date
     * @return string
     */
    public static function formatAsGregorian($date = null): string
    {
        if (is_null($date)) {
            $date = now('Asia/Kathmandu');
        } elseif (is_string($date)) {
            $date = Carbon::parse($date, 'Asia/Kathmandu');
        } elseif (!$date instanceof Carbon) {
            $date = Carbon::instance($date, 'Asia/Kathmandu');
        }

        return $date->format('l, F j, Y');
    }

    /**
     * Format a date as time (HH:MM:SS AM/PM).
     *
     * @param  \Carbon\Carbon|string|null  $date
     * @return string
     */
    public static function formatAsTime($date = null): string
    {
        if (is_null($date)) {
            $date = now('Asia/Kathmandu');
        } elseif (is_string($date)) {
            $date = Carbon::parse($date, 'Asia/Kathmandu');
        } elseif (!$date instanceof Carbon) {
            $date = Carbon::instance($date, 'Asia/Kathmandu');
        }

        return $date->format('g:i:s A');
    }

    /**
     * Get greeting based on current time.
     *
     * @param  \Carbon\Carbon|string|null  $date
     * @return string
     */
    public static function getTimeBasedGreeting($date = null): string
    {
        if (is_null($date)) {
            $date = now('Asia/Kathmandu');
        } elseif (is_string($date)) {
            $date = Carbon::parse($date, 'Asia/Kathmandu');
        } elseif (!$date instanceof Carbon) {
            $date = Carbon::instance($date, 'Asia/Kathmandu');
        }

        $hour = (int) $date->format('G'); // 24-hour format, 0-23

        if ($hour < 12) {
            return 'Good Morning';
        }

        if ($hour < 17) {
            return 'Good Afternoon';
        }

        return 'Good Evening';
    }
}

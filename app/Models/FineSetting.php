<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FineSetting extends Model
{
    use HasFactory;

    public const DEFAULTS = [
        'per_day_fine' => 5.00,
        'grace_period_days' => 2,
        'max_fine_amount' => 500.00,
        'lost_book_penalty' => 1000.00,
        'damaged_book_penalty' => 250.00,
        'fair_condition_penalty' => 50.00,
        'issue_duration_days' => 14,
        'max_books_per_student' => 5,
        'renewal_limit' => 2,
        'renewal_duration_days' => 7,
        'logo_fallback_text' => 'LMS',
        'is_active' => true,
    ];

    protected $fillable = [
        'per_day_fine',
        'grace_period_days',
        'max_fine_amount',
        'lost_book_penalty',
        'damaged_book_penalty',
        'fair_condition_penalty',
        'issue_duration_days',
        'max_books_per_student',
        'renewal_limit',
        'renewal_duration_days',
        'logo_path',
        'logo_fallback_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function defaults(?array $only = null): array
    {
        if ($only === null) {
            return self::DEFAULTS;
        }

        return array_intersect_key(self::DEFAULTS, array_flip($only));
    }

    public static function resolveActive(): self
    {
        $setting = static::query()->where('is_active', true)->first()
            ?? static::query()->first()
            ?? new static();

        return $setting->applyDefaults();
    }

    public function applyDefaults(): self
    {
        foreach (static::defaults() as $attribute => $defaultValue) {
            if ($this->getAttribute($attribute) === null) {
                $this->setAttribute($attribute, $defaultValue);
            }
        }

        return $this;
    }
}

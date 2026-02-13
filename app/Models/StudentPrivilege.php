<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPrivilege extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'max_books',
        'issue_duration_days',
        'per_day_fine',
        'borrowing_allowed',
    ];

    protected $casts = [
        'borrowing_allowed' => 'boolean',
        'max_books' => 'integer',
        'issue_duration_days' => 'integer',
        'per_day_fine' => 'decimal:2',
    ];

    /**
     * Get the student associated with this privilege record.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get effective max books (override or global default)
     */
    public function getEffectiveMaxBooks($globalDefault = 5)
    {
        return $this->max_books ?? $globalDefault;
    }

    /**
     * Get effective issue duration (override or global default)
     */
    public function getEffectiveIssueDuration($globalDefault = 14)
    {
        return $this->issue_duration_days ?? $globalDefault;
    }

    /**
     * Get effective per-day fine (override or global default)
     */
    public function getEffectivePerDayFine($globalDefault = 10)
    {
        return $this->per_day_fine ?? $globalDefault;
    }

    /**
     * Check if borrowing is allowed for this student
     */
    public function isBorrowingAllowed()
    {
        return $this->borrowing_allowed;
    }
}

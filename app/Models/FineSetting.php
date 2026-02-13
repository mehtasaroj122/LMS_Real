<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FineSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'per_day_fine',
        'grace_period_days',
        'max_fine_amount',
        'lost_book_penalty',
        'damaged_book_penalty',
        'issue_duration_days',
        'max_books_per_student',
        'is_active',
    ];
}

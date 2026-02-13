<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'issued_book_id',
        'student_id',
        'amount',
        'days_late',
        'status',
        'paid_on',
        'payment_method',
        'remarks',
    ];

    protected $casts = [
        'paid_on' => 'date',
    ];

    public function issuedBook()
    {
        return $this->belongsTo(IssuedBook::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

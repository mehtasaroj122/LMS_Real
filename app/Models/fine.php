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
        'paid_at',
        'paid_by',
        'waive_reason',
        'waived_at',
        'waived_by',
        'payment_method',
        'remarks',
    ];

    protected $casts = [
        'paid_on' => 'date',
        'paid_at' => 'datetime',
        'waived_at' => 'datetime',
    ];

    public function issuedBook()
    {
        return $this->belongsTo(IssuedBook::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function waivedBy()
    {
        return $this->belongsTo(User::class, 'waived_by');
    }
}

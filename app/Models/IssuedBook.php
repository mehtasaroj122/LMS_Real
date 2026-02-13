<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuedBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'student_id',
        'issued_by',
        'issue_date',
        'due_date',
        'return_date',
        'status',
        'condition',
        'fine_amount',
        'remarks',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function fine()
    {
        return $this->hasOne(Fine::class);
    }
}

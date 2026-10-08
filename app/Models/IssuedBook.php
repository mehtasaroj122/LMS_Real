<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuedBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'book_copy_id',
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

    public const ACTIVE_RETURN_STATUSES = ['issued', 'borrowed', 'overdue'];

    public function scopeReturnable($query)
    {
        return $query->whereNull('return_date')
            ->whereIn('issued_books.status', self::ACTIVE_RETURN_STATUSES)
            ->whereNotNull('issue_date')
            ->whereNotNull('due_date')
            ->whereDate('issue_date', '<=', today())
            ->whereColumn('due_date', '>=', 'issue_date')
            ->whereHas('student')
            ->whereHas('book')
            ->whereHas('bookCopy', fn ($copy) => $copy->where('status', 'issued')
                ->where('condition', '!=', 'lost')
                ->whereColumn('book_copies.book_id', 'issued_books.book_id'))
            ->whereNotExists(function ($other) {
                $other->selectRaw('1')->from('issued_books as other_issues')
                    ->whereColumn('other_issues.book_copy_id', 'issued_books.book_copy_id')
                    ->whereColumn('other_issues.id', '!=', 'issued_books.id')
                    ->whereNull('other_issues.return_date')
                    ->whereIn('other_issues.status', self::ACTIVE_RETURN_STATUSES);
            });
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function bookCopy()
    {
        return $this->belongsTo(BookCopy::class);
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

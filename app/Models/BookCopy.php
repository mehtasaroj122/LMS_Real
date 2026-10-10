<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookCopy extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'accession_number',
        'entry_date',
        'book_type',
        'status',
        'shelf_location',
        'condition',
        'price',
        'remarks',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function issuedBooks(): HasMany
    {
        return $this->hasMany(IssuedBook::class);
    }

    public function activeIssue(): HasOne
    {
        return $this->hasOne(IssuedBook::class)
            ->whereNull('return_date')
            ->latestOfMany('issue_date');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeWhereCirculationStatus(Builder $query, string $status): Builder
    {
        if (! in_array($status, ['available', 'issued'], true)) {
            return $query->where('status', $status);
        }

        $query->whereIn('status', ['available', 'issued']);

        return $status === 'issued'
            ? $query->whereHas('issuedBooks', fn ($issued) => $issued->whereNull('return_date'))
            : $query->whereDoesntHave('issuedBooks', fn ($issued) => $issued->whereNull('return_date'));
    }

    public function scopeAvailableForIssue(Builder $query): Builder
    {
        return $query->whereCirculationStatus('available')
            ->where('book_type', '!=', 'reference')
            ->whereNotIn('condition', ['damaged', 'lost']);
    }

    /** Circulation flags can drift after legacy imports; loan records determine possession. */
    public function circulationStatus(): string
    {
        if (! in_array($this->status, ['available', 'issued'], true)) {
            return $this->status;
        }

        $activeIssues = $this->getAttribute('active_issues_count');
        if ($activeIssues === null) {
            $activeIssues = $this->relationLoaded('issuedBooks')
                ? $this->issuedBooks->contains(fn (IssuedBook $issue) => $issue->return_date === null)
                : $this->issuedBooks()->whereNull('return_date')->exists();
        }

        return $activeIssues ? 'issued' : 'available';
    }

    public function isBorrowable(): bool
    {
        return strtolower((string) $this->book_type) !== 'reference';
    }
}

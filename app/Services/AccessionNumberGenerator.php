<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AccessionNumberGenerator
{
    public const PREFIX = 'ACC';

    public const DIGITS = 6;

    public const MAX_NUMBER = 999999;

    public function next(): string
    {
        return DB::transaction(function (): string {
            $sequence = DB::table('accession_sequences')
                ->where('prefix', self::PREFIX)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                try {
                    DB::table('accession_sequences')->insert([
                        'prefix' => self::PREFIX,
                        'next_number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (QueryException) {
                    // Another transaction created the row. The unique prefix
                    // constraint makes this safe under concurrent first use.
                }

                $sequence = DB::table('accession_sequences')
                    ->where('prefix', self::PREFIX)
                    ->lockForUpdate()
                    ->first();
            }

            if (DB::connection()->getDriverName() === 'sqlite') {
                $highestExisting = DB::table('book_copies')
                    ->where('accession_number', 'like', self::PREFIX.'-%')
                    ->pluck('accession_number')
                    ->map(fn (string $value) => (int) substr($value, strlen(self::PREFIX) + 1))
                    ->max();
            } else {
                $highestExisting = DB::table('book_copies')
                    ->where('accession_number', 'like', self::PREFIX.'-%')
                    ->selectRaw("MAX(CAST(SUBSTRING_INDEX(accession_number, '-', -1) AS UNSIGNED)) as highest")
                    ->value('highest');
            }

            $nextNumber = max((int) ($sequence->next_number ?? 0), (int) ($highestExisting ?? 0)) + 1;

            DB::table('accession_sequences')
                ->where('prefix', self::PREFIX)
                ->update([
                    'next_number' => $nextNumber,
                    'updated_at' => now(),
                ]);

            return $this->format($nextNumber);
        });
    }

    /**
     * Return a non-reserved preview of the next accession numbers.
     * Final numbers are always allocated by next() inside the create transaction.
     */
    public function preview(int $quantity): array
    {
        if ($quantity < 1) {
            return [];
        }

        return DB::transaction(function () use ($quantity): array {
            $sequence = DB::table('accession_sequences')
                ->where('prefix', self::PREFIX)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                DB::table('accession_sequences')->insert([
                    'prefix' => self::PREFIX,
                    'next_number' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $sequence = DB::table('accession_sequences')
                    ->where('prefix', self::PREFIX)
                    ->lockForUpdate()
                    ->first();
            }

            $highestExisting = DB::connection()->getDriverName() === 'sqlite'
                ? DB::table('book_copies')
                    ->where('accession_number', 'like', self::PREFIX.'-%')
                    ->pluck('accession_number')
                    ->map(fn (string $value) => (int) substr($value, strlen(self::PREFIX) + 1))
                    ->max()
                : DB::table('book_copies')
                    ->where('accession_number', 'like', self::PREFIX.'-%')
                    ->selectRaw("MAX(CAST(SUBSTRING_INDEX(accession_number, '-', -1) AS UNSIGNED)) as highest")
                    ->value('highest');

            $start = max((int) ($sequence->next_number ?? 0), (int) ($highestExisting ?? 0)) + 1;

            return collect(range($start, $start + $quantity - 1))
                ->map(fn (int $number) => $this->format($number))
                ->all();
        });
    }

    /**
     * Return the next accession without allocating or reserving it.
     */
    public function nextAvailable(): string
    {
        $sequenceNumber = (int) (DB::table('accession_sequences')
            ->where('prefix', self::PREFIX)
            ->value('next_number') ?? 0);

        return $this->format(max($sequenceNumber, $this->highestExistingNumber()) + 1);
    }

    public function format(int $number): string
    {
        if ($number < 1 || $number > self::MAX_NUMBER) {
            throw new \InvalidArgumentException('Accession number is outside the supported range.');
        }

        return self::PREFIX.'-'.str_pad((string) $number, self::DIGITS, '0', STR_PAD_LEFT);
    }

    public function parse(string $accession): ?int
    {
        $accession = strtoupper(trim($accession));
        $pattern = '/^'.preg_quote(self::PREFIX, '/').'-(\d{'.self::DIGITS.'})$/';

        if (! preg_match($pattern, $accession, $matches)) {
            return null;
        }

        $number = (int) $matches[1];

        return $number >= 1 && $number <= self::MAX_NUMBER ? $number : null;
    }

    /**
     * Build a non-reserving inclusive range in the canonical project format.
     */
    public function range(string $from, string $to): array
    {
        $start = $this->parse($from);
        $end = $this->parse($to);

        if ($start === null || $end === null || $end < $start) {
            throw new \InvalidArgumentException('Invalid accession range.');
        }

        return collect(range($start, $end))
            ->map(fn (int $number) => $this->format($number))
            ->all();
    }

    private function highestExistingNumber(): int
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return (int) (DB::table('book_copies')
                ->where('accession_number', 'like', self::PREFIX.'-%')
                ->pluck('accession_number')
                ->map(fn (string $value) => $this->parse($value) ?? 0)
                ->max() ?? 0);
        }

        return (int) (DB::table('book_copies')
            ->where('accession_number', 'like', self::PREFIX.'-%')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(accession_number, '-', -1) AS UNSIGNED)) as highest")
            ->value('highest') ?? 0);
    }
}

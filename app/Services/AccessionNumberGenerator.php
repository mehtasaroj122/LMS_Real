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

    /**
     * Build a quantity-based, non-reserving preview. When skipping is enabled,
     * quantity means printable labels and scanning continues past duplicates.
     */
    public function availableFrom(
        string $startAccession,
        int $quantity,
        bool $skipExisting = true,
        ?int $maximumScan = null
    ): array {
        $start = $this->parse($startAccession);
        $maximumScan ??= (int) config('accession-labels.max_scan', 2000);

        if ($start === null || $quantity < 1 || $quantity > (int) config('accession-labels.max_per_batch', 500)) {
            throw new \InvalidArgumentException('Invalid accession quantity request.');
        }

        $scanLimit = $skipExisting ? $maximumScan : $quantity;
        $lastPossible = min(self::MAX_NUMBER, $start + $scanLimit - 1);
        $first = $this->format($start);
        $last = $this->format($lastPossible);
        $existing = DB::table('book_copies')
            ->whereBetween('accession_number', [$first, $last])
            ->where('accession_number', 'like', self::PREFIX.'-%')
            ->pluck('accession_number')
            ->filter(fn (string $value) => $this->parse($value) !== null)
            ->flip();

        $labels = [];
        $skipped = [];
        $scanned = 0;
        $lastScanned = $start;

        for ($number = $start; $number <= $lastPossible; $number++) {
            $accession = $this->format($number);
            $lastScanned = $number;
            $scanned++;

            if ($existing->has($accession)) {
                $skipped[] = $accession;
            } else {
                $labels[] = $accession;
            }

            if ($skipExisting && count($labels) === $quantity) {
                break;
            }

            if (! $skipExisting && $scanned === $quantity) {
                break;
            }
        }

        return [
            'start' => $first,
            'last_scanned' => $this->format($lastScanned),
            'requested_quantity' => $quantity,
            'scanned_count' => $scanned,
            'labels' => $labels,
            'skipped' => $skipped,
            'fulfilled' => count($labels) === $quantity,
            'skip_existing' => $skipExisting,
        ];
    }

    public function analyzeRange(string $from, string $to): array
    {
        $requested = $this->range($from, $to);
        $existing = DB::table('book_copies')
            ->whereIn('accession_number', $requested)
            ->orderBy('accession_number')
            ->pluck('accession_number')
            ->all();
        $lookup = array_fill_keys($existing, true);

        return [
            'start' => $requested[0],
            'last_scanned' => $requested[count($requested) - 1],
            'requested_quantity' => count($requested),
            'scanned_count' => count($requested),
            'labels' => array_values(array_filter(
                $requested,
                fn (string $accession) => ! isset($lookup[$accession])
            )),
            'skipped' => $existing,
            'fulfilled' => count($existing) === 0,
            'skip_existing' => false,
        ];
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

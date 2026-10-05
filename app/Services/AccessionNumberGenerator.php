<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AccessionNumberGenerator
{
    public const PREFIX = 'ACC';

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
                    ->where('accession_number', 'like', self::PREFIX . '-%')
                    ->pluck('accession_number')
                    ->map(fn (string $value) => (int) substr($value, strlen(self::PREFIX) + 1))
                    ->max();
            } else {
                $highestExisting = DB::table('book_copies')
                    ->where('accession_number', 'like', self::PREFIX . '-%')
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

            return self::PREFIX . '-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
        });
    }
}

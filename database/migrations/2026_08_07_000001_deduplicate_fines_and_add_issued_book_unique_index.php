<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->removeDuplicateFineRows();

        Schema::table('fines', function (Blueprint $table) {
            $table->unique('issued_book_id', 'fines_issued_book_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('fines', function (Blueprint $table) {
            $table->dropUnique('fines_issued_book_id_unique');
        });
    }

    private function removeDuplicateFineRows(): void
    {
        $duplicateIssuedBookIds = DB::table('fines')
            ->select('issued_book_id')
            ->groupBy('issued_book_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('issued_book_id')
            ->pluck('issued_book_id');

        $duplicateIssuedBookIds
            ->chunk(100)
            ->each(function ($issuedBookIds) {
                foreach ($issuedBookIds as $issuedBookId) {
                    $rows = DB::table('fines')
                        ->where('issued_book_id', $issuedBookId)
                        ->get();

                    $keeper = $rows
                        ->sort(fn ($first, $second) => $this->sortFineRows($first, $second))
                        ->first();

                    if (!$keeper) {
                        continue;
                    }

                    $duplicateIds = $rows
                        ->pluck('id')
                        ->reject(fn ($id) => (int) $id === (int) $keeper->id)
                        ->values();

                    if ($duplicateIds->isNotEmpty()) {
                        DB::table('fines')->whereIn('id', $duplicateIds)->delete();
                    }
                }
            });
    }

    private function sortFineRows(object $first, object $second): int
    {
        return [
            $this->statusPriority((string) $second->status),
            (int) ($second->days_late ?? 0),
            (float) ($second->amount ?? 0),
            strtotime((string) ($second->updated_at ?? $second->created_at ?? '')) ?: 0,
            (int) $second->id,
        ] <=> [
            $this->statusPriority((string) $first->status),
            (int) ($first->days_late ?? 0),
            (float) ($first->amount ?? 0),
            strtotime((string) ($first->updated_at ?? $first->created_at ?? '')) ?: 0,
            (int) $first->id,
        ];
    }

    private function statusPriority(string $status): int
    {
        return match (strtolower($status)) {
            'paid' => 4,
            'waived' => 3,
            'pending' => 2,
            default => 1,
        };
    }
};

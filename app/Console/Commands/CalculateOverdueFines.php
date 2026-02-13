<?php

namespace App\Console\Commands;

use App\Models\IssuedBook;
use App\Services\FineCalculator;
use Illuminate\Console\Command;

class CalculateOverdueFines extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fines:calculate-overdue';

    /**
     * The console command description.
     */
    protected $description = 'Calculate fines for overdue books';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fineCalculator = new FineCalculator();

        // Get all unreturned books that are overdue
        $overdueBooks = IssuedBook::whereNull('return_date')
            ->where('due_date', '<', now())
            ->get();

        $this->info("Found {$overdueBooks->count()} overdue books.");

        $finesApplied = 0;
        $totalAmount = 0;

        foreach ($overdueBooks as $book) {
            $fine = $fineCalculator->applyFine($book);
            
            if ($fine) {
                $finesApplied++;
                $totalAmount += $fine->amount;
                
                $this->line("Fine applied: {$book->student->name} - ₹{$fine->amount} ({$fine->days_late} days late)");
            }
        }

        $this->info("-----------------------------------");
        $this->info("Total fines applied: {$finesApplied}");
        $this->info("Total amount: ₹{$totalAmount}");
    }
}

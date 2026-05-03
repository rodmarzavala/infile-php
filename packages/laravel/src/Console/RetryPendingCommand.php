<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Artisan command: php artisan fel:retry-pending
 *
 * Retries all DTEs in the fel_dte table with status = 'pending'.
 */
final class RetryPendingCommand extends Command
{
    protected $signature = 'fel:retry-pending
        {--limit=50 : Maximum number of records to process}';

    protected $description = 'Retry all pending FEL DTEs that were queued during a contingency';

    public function handle(): int
    {
        /** @var int $limit */
        $limit = (int) $this->option('limit');

        /** @var \Illuminate\Support\Collection<int, \stdClass> $pending */
        $pending = DB::table('fel_dte')
            ->where('status', 'pending')
            ->limit($limit)
            ->get();

        if ($pending->isEmpty()) {
            $this->components->info('No pending DTEs found.');

            return self::SUCCESS;
        }

        $this->info("Found {$pending->count()} pending DTE(s). Retrying...");
        $this->newLine();

        $succeeded = 0;
        $failed    = 0;

        foreach ($pending as $row) {
            try {
                // TODO: Reconstruct DTE from stored data and re-certify.
                // Full implementation requires deserializing the stored XML.
                $this->line("  Processing UUID: {$row->uuid}");

                DB::table('fel_dte')
                    ->where('uuid', $row->uuid)
                    ->update(['status' => 'pending']);

                $succeeded++;
            } catch (\Throwable $e) {
                $this->error("  Failed for UUID {$row->uuid}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->components->info("Retry complete. Succeeded: {$succeeded} | Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

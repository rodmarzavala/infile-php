<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Console;

use Illuminate\Console\Command;
use InfilePhp\Core\InfilePhp;

/**
 * Artisan command: php artisan fel:status
 *
 * Pings the Infile certification endpoint and reports health + response time.
 */
final class StatusCommand extends Command
{
    protected $signature = 'fel:status';

    protected $description = 'Check the health and response time of the Infile API';

    public function handle(): int
    {
        $this->info('Checking Infile API status...');
        $this->newLine();

        try {
            $ms = InfilePhp::client()->ping();

            $this->components->twoColumnDetail('<fg=green>Infile Certify Endpoint</>', "<fg=green>Online</> ({$ms}ms)");
            $this->newLine();
            $this->components->info('Infile is reachable. Your FEL integration is ready.');

            if (config('queue.default') === 'sync' && config('felkit.fallback.enabled', true)) {
                $this->newLine();
                $this->components->warn('Warning: Fallback is enabled but QUEUE_CONNECTION is "sync".');
                $this->line('  Failed DTEs will not be retried automatically in the background.');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->components->twoColumnDetail('<fg=red>Infile Certify Endpoint</>', '<fg=red>Unreachable</>');
            $this->newLine();
            $this->error("Error: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}

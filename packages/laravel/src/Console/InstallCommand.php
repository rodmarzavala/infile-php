<?php

declare(strict_types=1);

namespace InfilePhp\Laravel\Console;

use Illuminate\Console\Command;

/**
 * Artisan command: php artisan fel:install
 *
 * Publishes the FEL config file, runs migrations, and prints
 * the .env variable checklist with descriptions.
 */
final class InstallCommand extends Command
{
    protected $signature = 'fel:install';

    protected $description = 'Install and configure the infile-php FEL SDK';

    public function handle(): int
    {
        $this->info('Installing infile-php FEL SDK...');
        $this->newLine();

        // Publish config
        $this->call('vendor:publish', [
            '--tag'   => 'felkit-config',
            '--force' => false,
        ]);

        // Run migrations
        $this->call('migrate', ['--force' => false]);

        // Print .env checklist
        $this->newLine();
        $this->components->info('Add these variables to your .env file:');
        $this->newLine();

        $vars = [
            'FEL_ENV'             => 'Operating environment: sandbox or production',
            'FEL_NIT'             => 'Your company NIT registered with SAT Guatemala',
            'FEL_FLOW'            => 'Certification flow: unified (recommended) or separate',
            'FEL_SIGN_USER'       => 'UsuarioFirma / alias prefix provided by Infile',
            'FEL_SIGN_KEY'        => 'LlaveFirma / Token Signer obtained from SAT',
            'FEL_API_USER'        => 'UsuarioApi (same value as FEL_SIGN_USER)',
            'FEL_API_KEY'         => 'LlaveApi provided by Infile S.A.',
            'FEL_RETRY_TIMES'     => 'Number of retry attempts before triggering fallback (default: 3)',
            'FEL_RETRY_SLEEP'     => 'Seconds between retries, used as exponential base (default: 2)',
            'FEL_FALLBACK_ENABLED' => 'Enable CAFE contingency mode when Infile is unreachable (default: true)',
        ];

        foreach ($vars as $key => $description) {
            $this->line("  <fg=yellow>{$key}</> — {$description}");
        }

        $this->newLine();
        $this->components->info('Next steps:');
        $this->line('  1. Fill in the .env variables listed above.');
        $this->line('  2. Verify connectivity: php artisan fel:status');
        $this->line('  3. Use Invoice::create()->...->issue() to certify your first DTE.');
        $this->newLine();

        return self::SUCCESS;
    }
}

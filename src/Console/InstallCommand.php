<?php

namespace Auditify\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'auditify:install';

    protected $description = 'Install Auditify Package';

    public function handle(): int
    {
        $this->info('Installing Auditify...');

        $this->call('vendor:publish', [
            '--tag' => 'auditify-config',
            '--force' => true,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'auditify-migrations',
            '--force' => true,
        ]);

        $this->call('migrate');

        $this->newLine();
        $this->info('Clearing application caches...');
        $this->call('optimize:clear');

        $this->newLine();
        $this->info('Auditify Installed Successfully.');
        $this->comment('Tip: During local development, avoid running "php artisan config:cache" so that configuration changes are loaded instantly.');

        return self::SUCCESS;
    }
}

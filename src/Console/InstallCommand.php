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

        $this->info('Auditify Installed Successfully.');

        return self::SUCCESS;
    }
}

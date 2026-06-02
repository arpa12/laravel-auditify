<?php

namespace Auditify\Tests\Feature;

use Auditify\Tests\TestCase;
use Illuminate\Support\Facades\File;

class InstallationTest extends TestCase
{
    public function test_installer_runs_successfully(): void
    {
        // Clean up config file in case it exists in orchestra's test path
        $configPath = config_path('auditify.php');
        if (File::exists($configPath)) {
            File::delete($configPath);
        }

        $this->assertFalse(File::exists($configPath));

        // Run the installation command
        $this->artisan('auditify:install')
            ->expectsOutput('Installing Auditify...')
            ->expectsOutput('Auditify Installed Successfully.')
            ->assertExitCode(0);

        // Assert that the configuration file was copied
        $this->assertTrue(File::exists($configPath));

        // Clean up
        if (File::exists($configPath)) {
            File::delete($configPath);
        }
    }
}

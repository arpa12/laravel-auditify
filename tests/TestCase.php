<?php

namespace Auditify\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Auditify\AuditifyServiceProvider;
use Maatwebsite\Excel\ExcelServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app)
    {
        return [
            AuditifyServiceProvider::class,
            ExcelServiceProvider::class,
            \Barryvdh\DomPDF\ServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Enforce configurations for consistent testing
        $app['config']->set('auditify.route_prefix', 'auditify');
        $app['config']->set('auditify.authorization.enabled', false);
        $app['config']->set('auditify.auto_audit_models', false);
    }

    protected function setUpDatabase($app)
    {
        // Create users table for testing auth events and relationships
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }
}

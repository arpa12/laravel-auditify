<?php

namespace Auditify\Tests\Feature;

use Auditify\Tests\TestCase;
use Auditify\Tests\Models\User;
use Auditify\Models\ActionLog;
use Auditify\Models\ActivityLog;
use Auditify\Models\SecurityLog;
use Illuminate\Support\Facades\Gate;

class DashboardAndExportsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.providers.users.model' => User::class]);
    }

    public function test_dashboard_renders_successfully(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/auditify');

        $response->assertStatus(200);
        $response->assertSee('Overview Dashboard');
        $response->assertSee('Action Logs');
        $response->assertSee('Activity Logs');
        $response->assertSee('Security Logs');
        $response->assertSee('Recent User Activities');
        $response->assertSee('Recent Security Violations');
    }

    public function test_action_logs_view_and_exports(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $log = ActionLog::create([
            'user_id' => $user->id,
            'action' => 'UPDATE',
            'module' => 'Project',
            'description' => 'Updated project info',
            'old_values' => ['name' => 'Old Project'],
            'new_values' => ['name' => 'New Project'],
            'ip_address' => '127.0.0.1',
        ]);

        // Detail show
        $response = $this->actingAs($user)->get('/auditify/action-logs/' . $log->id);
        $response->assertStatus(200);
        $response->assertSee('Visual Diff');
        $response->assertSee('Old Project');
        $response->assertSee('New Project');

        // CSV Export
        $response = $this->actingAs($user)->get('/auditify/action-logs/export/csv');
        $response->assertStatus(200);
        $this->assertStringContainsString('Updated project info', $response->streamedContent());

        // Excel Export
        $response = $this->actingAs($user)->get('/auditify/action-logs/export/excel');
        $response->assertStatus(200);
    }

    public function test_activity_logs_view_and_exports(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'Login User',
            'ip_address' => '192.168.1.1',
        ]);

        // List
        $response = $this->actingAs($user)->get('/auditify/activity-logs');
        $response->assertStatus(200);
        $response->assertSee('Login User');

        // CSV Export
        $response = $this->actingAs($user)->get('/auditify/activity-logs/export/csv');
        $response->assertStatus(200);
        $this->assertStringContainsString('Login User', $response->streamedContent());

        // Excel Export
        $response = $this->actingAs($user)->get('/auditify/activity-logs/export/excel');
        $response->assertStatus(200);
    }

    public function test_security_logs_view_and_exports(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $log = SecurityLog::create([
            'user_id' => $user->id,
            'title' => 'Brute Force Attack',
            'description' => 'Suspected attempt detected',
            'severity' => 'critical',
            'is_read' => false,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'TestAgent/1.0',
        ]);

        // List
        $response = $this->actingAs($user)->get('/auditify/security-logs');
        $response->assertStatus(200);
        $response->assertSee('Brute Force Attack');
        $response->assertSee('192.168.1.100');

        // Show Details
        $response = $this->actingAs($user)->get('/auditify/security-logs/' . $log->id);
        $response->assertStatus(200);
        $response->assertSee('Brute Force Attack');
        $response->assertSee('critical');
        $response->assertSee('192.168.1.100');
        $response->assertSee('TestAgent/1.0');

        // Mark as Read
        $response = $this->actingAs($user)->post('/auditify/security-logs/' . $log->id . '/read');
        $response->assertStatus(302);
        $this->assertTrue($log->fresh()->is_read);

        // CSV Export
        $response = $this->actingAs($user)->get('/auditify/security-logs/export/csv');
        $response->assertStatus(200);
        $this->assertStringContainsString('Brute Force Attack', $response->streamedContent());
        $this->assertStringContainsString('192.168.1.100', $response->streamedContent());
        $this->assertStringContainsString('TestAgent/1.0', $response->streamedContent());

        // Excel Export
        $response = $this->actingAs($user)->get('/auditify/security-logs/export/excel');
        $response->assertStatus(200);
    }

    public function test_access_denied_when_authorization_enabled_and_gate_denies(): void
    {
        config(['auditify.authorization.enabled' => true]);
        config(['auditify.authorization.gate' => 'view-auditify']);

        Gate::define('view-auditify', fn ($user) => false);

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/auditify');
        $response->assertStatus(403);
    }

    public function test_access_allowed_when_authorization_enabled_and_gate_allows(): void
    {
        config(['auditify.authorization.enabled' => true]);
        config(['auditify.authorization.gate' => 'view-auditify']);

        Gate::define('view-auditify', fn ($user) => true);

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/auditify');
        $response->assertStatus(200);
    }
}

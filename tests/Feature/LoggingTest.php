<?php

namespace Auditify\Tests\Feature;

use Auditify\Tests\TestCase;
use Auditify\Tests\Models\User;
use Auditify\Models\ActionLog;
use Auditify\Models\ActivityLog;
use Auditify\Models\SecurityLog;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Notification;
use Auditify\Facades\Auditify;
use Auditify\Notifications\SuspiciousActivityAlert;

class LoggingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.providers.users.model' => User::class]);
    }

    public function test_log_action_creates_action_log_successfully(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        Auditify::logAction(
            'CREATE',
            'Project',
            'Created project named test',
            ['name' => 'Old Name'],
            ['name' => 'New Name'],
            $user->id
        );

        $this->assertDatabaseHas('audit_action_logs', [
            'action' => 'CREATE',
            'module' => 'Project',
            'description' => 'Created project named test',
            'user_id' => $user->id,
        ]);

        $log = ActionLog::first();
        $this->assertEquals(['name' => 'Old Name'], $log->old_values);
        $this->assertEquals(['name' => 'New Name'], $log->new_values);
    }

    public function test_log_activity_creates_activity_log_successfully(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        Auditify::logActivity('Search Logs', 'http://localhost/search', $user->id);

        $this->assertDatabaseHas('audit_activity_logs', [
            'activity' => 'Search Logs',
            'url' => 'http://localhost/search',
            'user_id' => $user->id,
        ]);
    }

    public function test_log_security_creates_security_log_successfully(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        Auditify::logSecurity('Suspicious Operation', 'User attempted illegal action', 'high', $user->id);

        $this->assertDatabaseHas('audit_security_logs', [
            'title' => 'Suspicious Operation',
            'description' => 'User attempted illegal action',
            'severity' => 'high',
            'user_id' => $user->id,
            'is_read' => 0,
        ]);

        $log = SecurityLog::first();
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_login_event_creates_activity_log(): void
    {
        $user = User::create([
            'name' => 'Login User',
            'email' => 'login@example.com',
            'password' => bcrypt('password'),
        ]);

        event(new \Illuminate\Auth\Events\Login('web', $user, false));

        $this->assertDatabaseHas('audit_activity_logs', [
            'activity' => 'Login: login@example.com',
            'user_id' => $user->id,
        ]);
    }

    public function test_logout_event_creates_activity_log(): void
    {
        $user = User::create([
            'name' => 'Logout User',
            'email' => 'logout@example.com',
            'password' => bcrypt('password'),
        ]);

        event(new \Illuminate\Auth\Events\Logout('web', $user));

        $this->assertDatabaseHas('audit_activity_logs', [
            'activity' => 'Logout: logout@example.com',
            'user_id' => $user->id,
        ]);
    }

    public function test_failed_login_event_creates_activity_log(): void
    {
        event(new \Illuminate\Auth\Events\Failed('web', null, [
            'email' => 'hacker@example.com',
            'password' => 'secret'
        ]));

        $this->assertDatabaseHas('audit_activity_logs', [
            'activity' => 'Failed Login: hacker@example.com',
        ]);
    }

    public function test_page_visit_creates_activity_log(): void
    {
        config(['auditify.track_page_visits' => true]);

        Route::get('/testing-page-visit', function () {
            return 'OK';
        })->middleware('web');

        $user = User::create([
            'name' => 'Page User',
            'email' => 'page@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/testing-page-visit');
        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_activity_logs', [
            'activity' => 'Page Visit: /testing-page-visit',
            'user_id' => $user->id,
        ]);
    }

    public function test_security_alert_triggers_on_sensitive_module(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        Auditify::logAction('UPDATE', 'Setting', 'Modified mail configuration settings', [], [], $user->id);

        $this->assertDatabaseHas('audit_security_logs', [
            'title' => 'Sensitive Module Changes',
            'severity' => 'medium',
            'user_id' => $user->id,
        ]);
    }

    public function test_security_alert_triggers_on_permission_changes(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        Auditify::logAction('CREATE', 'Permission', 'Added new permission policy', [], [], $user->id);

        $this->assertDatabaseHas('audit_security_logs', [
            'title' => 'Permission Changes',
            'severity' => 'high',
            'user_id' => $user->id,
        ]);
    }

    public function test_security_alert_triggers_on_mass_delete(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            Auditify::logAction('DELETE', 'Post', 'Deleted a blog post', [], [], $user->id);
        }

        $this->assertDatabaseHas('audit_security_logs', [
            'title' => 'Mass Delete Detected',
            'severity' => 'critical',
            'user_id' => $user->id,
        ]);
    }

    public function test_security_alert_triggers_on_bulk_update(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        for ($i = 0; $i < 10; $i++) {
            Auditify::logAction('UPDATE', 'Post', 'Updated a blog post description', [], [], $user->id);
        }

        $this->assertDatabaseHas('audit_security_logs', [
            'title' => 'Bulk Update Detected',
            'severity' => 'high',
            'user_id' => $user->id,
        ]);
    }

    public function test_security_alert_triggers_on_failed_login_threshold(): void
    {
        for ($i = 0; $i < 3; $i++) {
            Auditify::logActivity('Failed Login');
        }

        $this->assertDatabaseHas('audit_security_logs', [
            'title' => 'Multiple Failed Logins',
            'severity' => 'high',
        ]);
    }

    public function test_notification_sent_on_high_severity_alert(): void
    {
        config(['auditify.alerts.enabled' => true]);
        config(['auditify.alerts.recipients' => ['security@example.com']]);

        Notification::fake();

        Auditify::logSecurity('High Alert', 'Breach found', 'high');

        Notification::assertSentTo(
            new \Illuminate\Notifications\AnonymousNotifiable,
            SuspiciousActivityAlert::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'security@example.com' 
                    && $notification->log->title === 'High Alert';
            }
        );
    }

    public function test_without_auditing_bypasses_logging(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertTrue(Auditify::isAuditing());

        Auditify::withoutAuditing(function () use ($user) {
            $this->assertFalse(Auditify::isAuditing());

            Auditify::logAction('CREATE', 'Project', 'Log created inside withoutAuditing block');
            Auditify::logActivity('Search Logs inside block');
            Auditify::logSecurity('Suspicious inside block', 'Details');
        });

        $this->assertTrue(Auditify::isAuditing());

        $this->assertEquals(0, ActionLog::count());
        $this->assertEquals(0, ActivityLog::count());
        $this->assertEquals(0, SecurityLog::count());
    }

    public function test_auditify_prune_command_removes_old_records(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create log older than cutoff (e.g. 100 days old)
        $oldAction = ActionLog::create([
            'user_id' => $user->id,
            'action' => 'CREATE',
            'module' => 'Project',
            'description' => '100 days old action log',
            'created_at' => now()->subDays(100),
        ]);

        $oldActivity = ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'Login User',
            'created_at' => now()->subDays(100),
        ]);

        $oldSecurity = SecurityLog::create([
            'user_id' => $user->id,
            'title' => 'Suspicious Activity',
            'description' => 'Details',
            'created_at' => now()->subDays(100),
        ]);

        // Create log within cutoff (e.g. 10 days old)
        $newAction = ActionLog::create([
            'user_id' => $user->id,
            'action' => 'CREATE',
            'module' => 'Project',
            'description' => '10 days old action log',
            'created_at' => now()->subDays(10),
        ]);

        $newActivity = ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'Login User',
            'created_at' => now()->subDays(10),
        ]);

        $newSecurity = SecurityLog::create([
            'user_id' => $user->id,
            'title' => 'Suspicious Activity',
            'description' => 'Details',
            'created_at' => now()->subDays(10),
        ]);

        // Run the console command using keep_days = 90 (default)
        $this->artisan('auditify:prune')
            ->expectsOutputToContain('Pruning logs older than 90 days')
            ->expectsOutputToContain('Successfully pruned:')
            ->expectsOutputToContain('- 1 Action Logs')
            ->expectsOutputToContain('- 1 Activity Logs')
            ->expectsOutputToContain('- 1 Security Logs')
            ->assertExitCode(0);

        // Assert old records were deleted
        $this->assertDatabaseMissing('audit_action_logs', ['id' => $oldAction->id]);
        $this->assertDatabaseMissing('audit_activity_logs', ['id' => $oldActivity->id]);
        $this->assertDatabaseMissing('audit_security_logs', ['id' => $oldSecurity->id]);

        // Assert new records are still present
        $this->assertDatabaseHas('audit_action_logs', ['id' => $newAction->id]);
        $this->assertDatabaseHas('audit_activity_logs', ['id' => $newActivity->id]);
        $this->assertDatabaseHas('audit_security_logs', ['id' => $newSecurity->id]);

        // Try running with custom days option to prune the remaining 10-day old logs
        $this->artisan('auditify:prune', ['--days' => 5])
            ->expectsOutputToContain('Pruning logs older than 5 days')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('audit_action_logs', ['id' => $newAction->id]);
        $this->assertDatabaseMissing('audit_activity_logs', ['id' => $newActivity->id]);
        $this->assertDatabaseMissing('audit_security_logs', ['id' => $newSecurity->id]);
    }

    public function test_polymorphic_user_logging(): void
    {
        $admin = \Auditify\Tests\Models\Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // 1. Log Action using Admin Model
        $actionLog = Auditify::logAction(
            'UPDATE',
            'Setting',
            'Changed setting',
            [],
            [],
            $admin
        );

        $this->assertEquals($admin->id, $actionLog->user_id);
        $this->assertEquals(get_class($admin), $actionLog->user_type);
        $this->assertInstanceOf(\Auditify\Tests\Models\Admin::class, $actionLog->user);
        $this->assertEquals('Admin User', $actionLog->user->name);

        // 2. Log Activity using Admin Model
        $activityLog = Auditify::logActivity(
            'Access Settings',
            null,
            $admin
        );

        $this->assertEquals($admin->id, $activityLog->user_id);
        $this->assertEquals(get_class($admin), $activityLog->user_type);
        $this->assertInstanceOf(\Auditify\Tests\Models\Admin::class, $activityLog->user);

        // 3. Log Security using Admin Model
        $securityLog = Auditify::logSecurity(
            'Unauthorized Settings Access',
            'Tried to access settings',
            'high',
            $admin
        );

        $this->assertEquals($admin->id, $securityLog->user_id);
        $this->assertEquals(get_class($admin), $securityLog->user_type);
        $this->assertInstanceOf(\Auditify\Tests\Models\Admin::class, $securityLog->user);
    }

    public function test_logging_with_explicit_array_context(): void
    {
        // Log using explicit ID and type array
        $actionLog = Auditify::logAction(
            'DELETE',
            'Project',
            'Deleted project',
            [],
            [],
            ['id' => 999, 'type' => 'App\Models\CustomUser']
        );

        $this->assertEquals(999, $actionLog->user_id);
        $this->assertEquals('App\Models\CustomUser', $actionLog->user_type);
    }
}

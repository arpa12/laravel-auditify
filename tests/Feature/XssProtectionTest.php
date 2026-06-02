<?php

namespace Auditify\Tests\Feature;

use Auditify\Tests\TestCase;
use Auditify\Tests\Models\User;
use Auditify\Models\SecurityLog;
use Illuminate\Support\Facades\Route;

class XssProtectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.providers.users.model' => User::class]);

        // Register dummy test routes using the 'web' middleware group (which loads BlockXssAttacks)
        Route::middleware('web')->group(function () {
            Route::post('/test-submit', function () {
                return response()->json(['status' => 'ok']);
            });

            Route::get('/test-route-param/{param}', function ($param) {
                return response()->json(['status' => 'ok', 'param' => $param]);
            });
        });
    }

    public function test_safe_inputs_are_allowed_through(): void
    {
        $response = $this->postJson('/test-submit', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Hello, this is a safe message.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
        $this->assertEquals(0, SecurityLog::count());
    }

    public function test_xss_inputs_are_blocked_and_logged(): void
    {
        $response = $this->postJson('/test-submit', [
            'name' => 'John Doe',
            'message' => '<script>alert("hack")</script>',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('audit_security_logs', [
            'title' => 'XSS Attack Attempt Detected',
            'severity' => 'critical',
        ]);

        $log = SecurityLog::first();
        $this->assertStringContainsString('message', $log->description);
        $this->assertStringContainsString('<script>alert("hack")</script>', $log->description);
    }

    public function test_xss_javascript_uri_is_blocked(): void
    {
        $response = $this->postJson('/test-submit', [
            'url' => 'javascript:alert(1)',
        ]);

        $response->assertStatus(403);
    }

    public function test_xss_inline_event_handler_is_blocked(): void
    {
        $response = $this->postJson('/test-submit', [
            'content' => '<img src="x" onerror="alert(1)">',
        ]);

        $response->assertStatus(403);
    }

    public function test_xss_route_parameters_are_scanned_and_blocked(): void
    {
        $response = $this->get('/test-route-param/' . urlencode('<script>'));

        $response->assertStatus(403);
    }

    public function test_xss_protection_can_be_disabled_in_config(): void
    {
        config(['auditify.xss_protection.enabled' => false]);

        $response = $this->postJson('/test-submit', [
            'message' => '<script>alert("hack")</script>',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, SecurityLog::count());
    }

    public function test_xss_only_logged_without_blocking_when_configured(): void
    {
        config(['auditify.xss_protection.block' => false]);

        $response = $this->postJson('/test-submit', [
            'message' => '<script>alert("hack")</script>',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('audit_security_logs', [
            'title' => 'XSS Attack Attempt Detected',
            'severity' => 'critical',
        ]);
    }

    public function test_xss_protection_can_exclude_routes(): void
    {
        config(['auditify.xss_protection.exclude_routes' => ['test-submit']]);

        $response = $this->postJson('/test-submit', [
            'message' => '<script>alert("hack")</script>',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, SecurityLog::count());
    }
}

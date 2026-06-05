<?php

namespace Auditify\Services;

use Auditify\Models\ActionLog;
use Auditify\Models\ActivityLog;
use Auditify\Models\SecurityLog;
use Auditify\Notifications\SuspiciousActivityAlert;
use Illuminate\Support\Facades\Notification;

class AuditifyService
{
    /**
     * The callback that should be used to authorize Auditify views.
     *
     * @var \Closure|null
     */
    public static $authCallback;

    /**
     * Register the Auditify authorization callback.
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function auth(\Closure $callback)
    {
        static::$authCallback = $callback;

        return $this;
    }

    /**
     * Determine if the given request can access the Auditify dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function checkAuth($request): bool
    {
        if (static::$authCallback) {
            return (static::$authCallback)($request);
        }

        if (! config('auditify.authorization.enabled', false)) {
            return true;
        }

        $gate = config('auditify.authorization.gate', 'view-auditify');

        return \Illuminate\Support\Facades\Gate::allows($gate);
    }

    /**
     * Flag indicating if auditing is enabled.
     *
     * @var bool
     */
    protected $isAuditing = true;

    /**
     * Temporarily disable auditing.
     */
    public function disableAuditing(): void
    {
        $this->isAuditing = false;
    }

    /**
     * Temporarily enable auditing.
     */
    public function enableAuditing(): void
    {
        $this->isAuditing = true;
    }

    /**
     * Check if auditing is active.
     */
    public function isAuditing(): bool
    {
        return $this->isAuditing;
    }

    /**
     * Run the callback with auditing disabled.
     */
    public function withoutAuditing(callable $callback)
    {
        $wasAuditing = $this->isAuditing();
        
        $this->disableAuditing();

        try {
            return $callback();
        } finally {
            if ($wasAuditing) {
                $this->enableAuditing();
            }
        }
    }

    /**
     * Mask sensitive values (like passwords/tokens) in array.
     */
    protected function maskSensitiveValues(array $values): array
    {
        $sensitiveKeys = config('auditify.sensitive_fields', ['password', 'password_confirmation', 'token', 'secret', 'card', 'cvv', 'ssn']);

        return collect($values)->mapWithKeys(function ($value, $key) use ($sensitiveKeys) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                return [$key => '********'];
            }
            if (is_array($value)) {
                return [$key => $this->maskSensitiveValues($value)];
            }
            return [$key => $value];
        })->all();
    }

    /**
     * Log a database data change action (Module 1).
     */
    public function logAction(
        string $action,
        string $module,
        string $description,
        array $oldValues = [],
        array $newValues = [],
        $userId = null,
        $subject = null
    ): ActionLog {
        if (!$this->isAuditing()) {
            return new ActionLog();
        }

        if ($userId instanceof \Illuminate\Database\Eloquent\Model) {
            $resolvedUserId = $userId->getKey();
            $resolvedUserType = get_class($userId);
        } elseif (is_array($userId) && isset($userId['id'], $userId['type'])) {
            $resolvedUserId = $userId['id'];
            $resolvedUserType = $userId['type'];
        } else {
            $user = auth()->user();
            $resolvedUserId = $userId ?? ($user ? $user->getKey() : null);
            $resolvedUserType = $user && $resolvedUserId === $user->getKey()
                ? get_class($user)
                : ($resolvedUserId ? config('auth.providers.users.model') : null);
        }

        if ($subject instanceof \Illuminate\Database\Eloquent\Model) {
            $resolvedSubjectId = $subject->getKey();
            $resolvedSubjectType = get_class($subject);
        } elseif (is_array($subject) && isset($subject['id'], $subject['type'])) {
            $resolvedSubjectId = $subject['id'];
            $resolvedSubjectType = $subject['type'];
        } else {
            $resolvedSubjectId = null;
            $resolvedSubjectType = null;
        }

        $maskedOld = $this->maskSensitiveValues($oldValues);
        $maskedNew = $this->maskSensitiveValues($newValues);

        $log = ActionLog::create([
            'user_id' => $resolvedUserId,
            'user_type' => $resolvedUserType,
            'subject_id' => $resolvedSubjectId,
            'subject_type' => $resolvedSubjectType,
            'action' => strtoupper($action),
            'module' => $module,
            'description' => $description,
            'old_values' => !empty($maskedOld) ? $maskedOld : null,
            'new_values' => !empty($maskedNew) ? $maskedNew : null,
            'ip_address' => request()->ip(),
            'url' => request()->fullUrl(),
            'user_agent' => request()->userAgent(),
        ]);

        // Run automated security checks
        $this->runActionSecurityChecks($log);

        return $log;
    }

    /**
     * Log a user activity (Module 2).
     */
    public function logActivity(
        string $activity,
        ?string $url = null,
        $userId = null,
        array $properties = []
    ): ActivityLog {
        if (!$this->isAuditing()) {
            return new ActivityLog();
        }

        if ($userId instanceof \Illuminate\Database\Eloquent\Model) {
            $resolvedUserId = $userId->getKey();
            $resolvedUserType = get_class($userId);
        } elseif (is_array($userId) && isset($userId['id'], $userId['type'])) {
            $resolvedUserId = $userId['id'];
            $resolvedUserType = $userId['type'];
        } else {
            $user = auth()->user();
            $resolvedUserId = $userId ?? ($user ? $user->getKey() : null);
            $resolvedUserType = $user && $resolvedUserId === $user->getKey()
                ? get_class($user)
                : ($resolvedUserId ? config('auth.providers.users.model') : null);
        }

        $log = ActivityLog::create([
            'user_id' => $resolvedUserId,
            'user_type' => $resolvedUserType,
            'activity' => $activity,
            'properties' => $properties,
            'url' => $url ?? request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Run automated security checks
        $this->runActivitySecurityChecks($log);

        return $log;
    }

    /**
     * Log a security warning violation (Module 3).
     */
    public function logSecurity(
        string $title,
        string $description,
        string $severity = 'medium',
        $userId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $status = 'pending',
        $resolvedAt = null,
        ?string $resolutionNotes = null,
        ?string $method = null,
        ?string $routeName = null,
        ?array $payload = null
    ): SecurityLog {
        if (!$this->isAuditing()) {
            return new SecurityLog();
        }
        $ip = $ipAddress ?? request()->ip();
        if (!$ip) {
            preg_match('/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/', $description, $ipMatches);
            $ip = $ipMatches[0] ?? null;
        }

        if ($userId instanceof \Illuminate\Database\Eloquent\Model) {
            $resolvedUserId = $userId->getKey();
            $resolvedUserType = get_class($userId);
        } elseif (is_array($userId) && isset($userId['id'], $userId['type'])) {
            $resolvedUserId = $userId['id'];
            $resolvedUserType = $userId['type'];
        } else {
            $user = auth()->user();
            $resolvedUserId = $userId ?? ($user ? $user->getKey() : null);
            $resolvedUserType = $user && $resolvedUserId === $user->getKey()
                ? get_class($user)
                : ($resolvedUserId ? config('auth.providers.users.model') : null);
        }

        $log = SecurityLog::create([
            'user_id' => $resolvedUserId,
            'user_type' => $resolvedUserType,
            'severity' => strtolower($severity),
            'title' => $title,
            'description' => $description,
            'is_read' => false,
            'status' => $status ?? 'pending',
            'resolved_at' => $resolvedAt,
            'resolution_notes' => $resolutionNotes,
            'method' => $method ?? request()->method(),
            'route_name' => $routeName ?? (request()->route() ? request()->route()->getName() : null),
            'payload' => $payload ?? $this->maskSensitiveValues(request()->except(config('auditify.sensitive_fields', ['password', 'password_confirmation', 'token', 'secret', 'card', 'cvv', 'ssn']))),
            'ip_address' => $ip,
            'user_agent' => $userAgent ?? request()->userAgent(),
        ]);

        // Trigger notification alert if enabled and severity is high or critical
        if (config('auditify.alerts.enabled', false) && in_array(strtolower($severity), ['high', 'critical'])) {
            $this->sendSecurityAlertNotification($log);
        }

        return $log;
    }

    /**
     * Legacy log method for backward compatibility.
     */
    public function log(
        string $action,
        string $module,
        string $description,
        array $oldValues = [],
        array $newValues = [],
        $userId = null
    ): ActionLog {
        return $this->logAction($action, $module, $description, $oldValues, $newValues, $userId);
    }

    /**
     * Run automated security checks on Action logs.
     */
    protected function runActionSecurityChecks(ActionLog $log): void
    {
        $userId = $log->user_id;
        $userType = $log->user_type;
        if (!$userId) {
            return;
        }

        $userContext = ['id' => $userId, 'type' => $userType];

        // 1. Mass Delete Check
        if ($log->action === 'DELETE') {
            $threshold = config('auditify.alerts.thresholds.mass_delete', 5);
            $timeframeMinutes = config('auditify.alerts.thresholds.mass_delete_timeframe', 5);
            $timeframe = now()->subMinutes($timeframeMinutes);

            $deleteCount = ActionLog::where('action', 'DELETE')
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->where('module', $log->module)
                ->where('created_at', '>=', $timeframe)
                ->count();

            if ($deleteCount >= $threshold) {
                $alreadyLogged = SecurityLog::where('title', 'Mass Delete Detected')
                    ->where('user_id', $userId)
                    ->where('user_type', $userType)
                    ->where('created_at', '>=', now()->subMinutes(1))
                    ->exists();

                if (!$alreadyLogged) {
                    $this->logSecurity(
                        'Mass Delete Detected',
                        "User ID [{$userId}] deleted {$deleteCount} records in module [{$log->module}] within {$timeframeMinutes} minutes.",
                        'critical',
                        $userContext,
                        $log->ip_address,
                        $log->user_agent
                    );
                }
            }
        }

        // 2. Bulk Update Check
        if ($log->action === 'UPDATE') {
            $threshold = config('auditify.alerts.thresholds.bulk_update', 10);
            $timeframeMinutes = config('auditify.alerts.thresholds.bulk_update_timeframe', 5);
            $timeframe = now()->subMinutes($timeframeMinutes);

            $updateCount = ActionLog::where('action', 'UPDATE')
                ->where('user_id', $userId)
                ->where('user_type', $userType)
                ->where('module', $log->module)
                ->where('created_at', '>=', $timeframe)
                ->count();

            if ($updateCount >= $threshold) {
                $alreadyLogged = SecurityLog::where('title', 'Bulk Update Detected')
                    ->where('user_id', $userId)
                    ->where('user_type', $userType)
                    ->where('created_at', '>=', now()->subMinutes(1))
                    ->exists();

                if (!$alreadyLogged) {
                    $this->logSecurity(
                        'Bulk Update Detected',
                        "User ID [{$userId}] updated {$updateCount} records in module [{$log->module}] within {$timeframeMinutes} minutes.",
                        'high',
                        $userContext,
                        $log->ip_address,
                        $log->user_agent
                    );
                }
            }
        }

        // 3. Sensitive Module Change Check
        $sensitiveModules = config('auditify.alerts.sensitive_modules', ['User', 'Role', 'Permission', 'Setting', 'Config']);
        if (in_array($log->module, $sensitiveModules) && in_array($log->action, ['CREATE', 'UPDATE', 'DELETE'])) {
            $this->logSecurity(
                'Sensitive Module Changes',
                "Modifications on sensitive module [{$log->module}] via action [{$log->action}]: {$log->description}",
                'medium',
                $userContext,
                $log->ip_address,
                $log->user_agent
            );
        }

        // 4. Permission Changes Check
        if (in_array(strtolower($log->module), ['permission', 'role', 'gate']) || str_contains(strtolower($log->description), 'permission') || str_contains(strtolower($log->description), 'role')) {
            if (in_array($log->action, ['CREATE', 'UPDATE', 'DELETE'])) {
                $this->logSecurity(
                    'Permission Changes',
                    "Permission or role mapping altered: [{$log->action}] action on module [{$log->module}]. Details: {$log->description}",
                    'high',
                    $userContext,
                    $log->ip_address,
                    $log->user_agent
                );
            }
        }
    }

    /**
     * Run automated security checks on Activity logs.
     */
    protected function runActivitySecurityChecks(ActivityLog $log): void
    {
        // Multiple Failed Logins Check
        if ($log->activity === 'Failed Login' || str_starts_with($log->activity, 'Failed Login')) {
            $threshold = config('auditify.alerts.thresholds.failed_logins', 3);
            $timeframeMinutes = config('auditify.alerts.thresholds.failed_logins_timeframe', 5);
            $timeframe = now()->subMinutes($timeframeMinutes);

            $failedCount = ActivityLog::where('created_at', '>=', $timeframe)
                ->where(function ($query) use ($log) {
                    $query->where('activity', $log->activity);
                    if ($log->ip_address) {
                        $query->orWhere(function ($q) use ($log) {
                            $q->where('ip_address', $log->ip_address)
                              ->where(function ($subQ) {
                                  $subQ->where('activity', 'Failed Login')
                                       ->orWhere('activity', 'like', 'Failed Login:%');
                              });
                        });
                    }
                })
                ->count();

            if ($failedCount >= $threshold) {
                $alreadyLogged = SecurityLog::where('title', 'Multiple Failed Logins')
                    ->where('created_at', '>=', now()->subMinutes(1))
                    ->exists();

                if (!$alreadyLogged) {
                    $this->logSecurity(
                        'Multiple Failed Logins',
                        "Multiple failed login attempts detected ({$failedCount} failures within the last {$timeframeMinutes} minutes).",
                        'high',
                        $log->user ? $log->user : ($log->user_id ? ['id' => $log->user_id, 'type' => $log->user_type] : null),
                        $log->ip_address,
                        $log->user_agent
                    );
                }
            }
        }
    }

    /**
     * Send email notifications to configured recipients.
     */
    protected function sendSecurityAlertNotification(SecurityLog $log): void
    {
        $recipients = config('auditify.alerts.recipients', ['admin@example.com']);

        foreach ($recipients as $recipient) {
            Notification::route('mail', $recipient)
                ->notify(new SuspiciousActivityAlert($log, $log->description));
        }
    }

    /**
     * Audit a model event centrally.
     */
    public function auditModel(string $action, \Illuminate\Database\Eloquent\Model $model): void
    {
        $action = strtolower($action);
        $oldValues = [];
        $newValues = [];

        $actionMap = [
            'created' => 'CREATE',
            'updated' => 'UPDATE',
            'deleted' => 'DELETE',
            'restored' => 'RESTORE',
        ];

        $mappedAction = $actionMap[$action] ?? strtoupper($action);

        if ($action === 'updated') {
            $changes = $model->getChanges();
            foreach ($changes as $field => $newValue) {
                if ($field === 'updated_at') {
                    continue;
                }
                $oldValues[$field] = $model->getOriginal($field);
                $newValues[$field] = $newValue;
            }

            if (empty($newValues)) {
                return;
            }
        } elseif ($action === 'created') {
            $newValues = $model->toArray();
        } elseif ($action === 'deleted') {
            $oldValues = $model->toArray();
        } elseif ($action === 'restored') {
            $newValues = $model->toArray();
        } else {
            return;
        }

        $this->logAction(
            $mappedAction,
            class_basename($model),
            class_basename($model) . ' ' . $action,
            $oldValues,
            $newValues,
            null,
            $model
        );
    }
}

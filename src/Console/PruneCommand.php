<?php

namespace Auditify\Console;

use Illuminate\Console\Command;
use Auditify\Models\ActionLog;
use Auditify\Models\ActivityLog;
use Auditify\Models\SecurityLog;

class PruneCommand extends Command
{
    protected $signature = 'auditify:prune {--days= : Override the configured days to keep records}';

    protected $description = 'Prune old logs from Auditify database tables';

    public function handle(): int
    {
        $days = $this->option('days') ?: config('auditify.pruning.keep_days', 90);
        $cutoff = now()->subDays((int)$days);

        $this->info("Pruning logs older than {$days} days (created before {$cutoff->format('Y-m-d H:i:s')})...");

        $deletedActions = ActionLog::where('created_at', '<', $cutoff)->delete();
        $deletedActivities = ActivityLog::where('created_at', '<', $cutoff)->delete();
        $deletedSecurity = SecurityLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Successfully pruned:");
        $this->line("- {$deletedActions} Action Logs");
        $this->line("- {$deletedActivities} Activity Logs");
        $this->line("- {$deletedSecurity} Security Logs");

        return self::SUCCESS;
    }
}

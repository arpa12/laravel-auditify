@extends('auditify::layout')

@section('title', 'Auditify Overview Dashboard')
@section('header_title', 'Overview Dashboard')

@section('content')
<style>
    /* CSS Variables matching the global layout */
    .grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }
    
    /* Modern Glassmorphic KPI Cards */
    .kpi-card {
        background: linear-gradient(135deg, var(--bg-secondary), rgba(255, 255, 255, 0.01));
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 120px;
        transition: var(--transition-smooth);
        position: relative;
        overflow: hidden;
        min-width: 0;
    }
    .kpi-card:hover {
        border-color: var(--border-hover);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .kpi-card::after {
        content: '';
        position: absolute;
        bottom: -20px;
        right: -20px;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
        pointer-events: none;
        opacity: 0.5;
    }
    .kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    .kpi-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.75px;
    }
    .kpi-value {
        font-size: 2.1rem;
        font-weight: 800;
        color: var(--kpi-text-color);
        line-height: 1.1;
        letter-spacing: -0.03em;
    }
    .kpi-subtext {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .icon-action {
        background-color: rgba(99, 102, 241, 0.08);
        color: var(--color-indigo);
    }
    .icon-activity {
        background-color: rgba(59, 130, 246, 0.08);
        color: #3b82f6;
    }
    .icon-security {
        background-color: rgba(239, 68, 68, 0.08);
        color: #ef4444;
    }
    .icon-unread {
        background-color: rgba(245, 158, 11, 0.08);
        color: #f59e0b;
    }
    .kpi-icon svg {
        width: 18px;
        height: 18px;
        fill: currentColor;
    }

    /* Grids & Dashboard Layout */
    .grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-bottom: 28px;
    }
    .grid-2-1 {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 28px;
    }
    
    .card {
        background-color: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: 24px;
        box-shadow: var(--shadow-sm);
        transition: var(--transition-smooth);
        min-width: 0;
        overflow: hidden;
    }
    
    .widget-title {
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: var(--card-text-highlight);
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 12px;
    }
    .widget-title svg {
        width: 16px;
        height: 16px;
        fill: var(--text-secondary);
    }

    /* Breakdown lists */
    .breakdown-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }
    .breakdown-item {
        background-color: var(--box-bg-light);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 12px;
        text-align: center;
        transition: var(--transition-smooth);
    }
    .breakdown-item:hover {
        border-color: var(--border-hover);
        background-color: var(--bg-primary);
    }
    .breakdown-label {
        font-size: 0.725rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 4px;
        letter-spacing: 0.5px;
    }
    .breakdown-val {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--card-text-highlight);
    }

    /* Timelines & Item groups */
    .timeline-container {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .timeline-item {
        display: flex;
        gap: 12px;
        position: relative;
    }
    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 15px;
        top: 32px;
        bottom: -20px;
        width: 1px;
        background-color: var(--border-color);
    }
    .timeline-badge {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: var(--box-bg-light);
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
        z-index: 1;
    }
    .timeline-content {
        flex-grow: 1;
        background-color: var(--box-bg-light);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 10px 14px;
        transition: var(--transition-smooth);
    }
    .timeline-content:hover {
        border-color: var(--border-hover);
        background-color: var(--bg-primary);
    }
    .timeline-title {
        font-size: 0.825rem;
        font-weight: 600;
        color: var(--card-text-highlight);
    }
    .timeline-desc {
        font-size: 0.775rem;
        color: var(--text-secondary);
        margin-top: 2px;
    }
    .timeline-meta {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 6px;
        display: flex;
        gap: 10px;
    }

    /* Security Logs Alert list */
    .security-timeline {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .security-item {
        background-color: rgba(239, 68, 68, 0.02);
        border: 1px solid rgba(239, 68, 68, 0.1);
        border-radius: 8px;
        padding: 12px 16px;
        display: flex;
        gap: 12px;
        transition: var(--transition-smooth);
        text-decoration: none;
        align-items: flex-start;
    }
    .security-item:hover {
        border-color: rgba(239, 68, 68, 0.25);
        background-color: rgba(239, 68, 68, 0.05);
        transform: translateX(2px);
    }
    .security-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-top: 6px;
        flex-shrink: 0;
    }
    .indicator-critical { background-color: #ef4444; box-shadow: 0 0 6px #ef4444; }
    .indicator-high { background-color: #f97316; box-shadow: 0 0 6px #f97316; }
    .indicator-medium { background-color: #eab308; box-shadow: 0 0 6px #eab308; }
    .indicator-low { background-color: #3b82f6; box-shadow: 0 0 6px #3b82f6; }

    .security-details {
        flex-grow: 1;
    }
    .security-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--card-text-highlight);
    }
    .security-desc {
        font-size: 0.775rem;
        color: var(--text-secondary);
        margin-top: 2px;
    }
    .security-meta {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 6px;
    }

    /* Top list visual layout */
    .user-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .user-item {
        display: flex;
        align-items: center;
        gap: 12px;
        background-color: var(--box-bg-light);
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        transition: var(--transition-smooth);
    }
    .user-item:hover {
        border-color: var(--border-hover);
        background-color: var(--bg-primary);
    }
    .user-avatar {
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, var(--color-indigo), #0ea5e9);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
    }
    .user-info {
        flex-grow: 1;
        min-width: 0;
    }
    .user-name {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--card-text-highlight);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .user-email {
        font-size: 0.7rem;
        color: var(--text-muted);
    }
    .user-count {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--color-indigo);
        background-color: rgba(99, 102, 241, 0.08);
        padding: 2px 6px;
        border-radius: 4px;
    }

    /* Modules list progress bar */
    .module-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .module-item {
        display: flex;
        flex-direction: column;
    }
    .module-info {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        margin-bottom: 4px;
    }
    .module-name {
        font-weight: 500;
        color: var(--text-secondary);
    }
    .module-count {
        font-weight: 700;
        color: var(--card-text-highlight);
    }
    .progress-bar {
        height: 4px;
        background-color: var(--progress-bg);
        border-radius: 2px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--color-indigo), #0ea5e9);
        border-radius: 2px;
    }

    @media (max-width: 1200px) {
        .grid-4 { grid-template-columns: repeat(2, 1fr); }
        .grid-2-1 { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .grid-2 { grid-template-columns: 1fr; }
        .grid-4 { grid-template-columns: 1fr; }
        .breakdown-list { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
        .breakdown-list { grid-template-columns: 1fr; }
        .chart-container { height: 240px !important; }
        .card { padding: 16px; }
        .kpi-card { padding: 16px; min-height: 100px; }
        .kpi-value { font-size: 1.8rem; }
    }
</style>

<!-- Top Metrics KPIs -->
<div class="grid-4">
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Action Traces</span>
            <div class="kpi-icon icon-action">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 14h6v-6H4v6zm0 5h6v-6H4v6zM4 9h6V3H4v6zm9 5h7v-2h-7v2zm0 5h7v-2h-7v2zM13 5v2h7V5h-7z"/>
                </svg>
            </div>
        </div>
        <span class="kpi-value">{{ number_format($totalActionLogs) }}</span>
        <div class="kpi-subtext">
            <span>🟢</span> <span>Database operations logged</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">User Activities</span>
            <div class="kpi-icon icon-activity">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                </svg>
            </div>
        </div>
        <span class="kpi-value">{{ number_format($totalActivityLogs) }}</span>
        <div class="kpi-subtext">
            <span>🔵</span> <span>Sessions & visits traced</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Security Incidents</span>
            <div class="kpi-icon icon-security">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-1 6h2v6h-2V7zm0 8h2v2h-2v-2z"/>
                </svg>
            </div>
        </div>
        <span class="kpi-value">{{ number_format($totalSecurityLogs) }}</span>
        <div class="kpi-subtext">
            <span>🔴</span> <span>Total anomalies flagged</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Active Warnings</span>
            <div class="kpi-icon icon-unread">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
        </div>
        <span class="kpi-value" style="color: {{ $unreadSecurityLogsCount > 0 ? '#f59e0b' : 'var(--kpi-text-color)' }}">{{ number_format($unreadSecurityLogsCount) }}</span>
        <div class="kpi-subtext">
            @if($unreadSecurityLogsCount > 0)
                <span style="color:#f59e0b;">⚠️ Requires attention</span>
            @else
                <span>⚪ No pending alerts</span>
            @endif
        </div>
    </div>
</div>

<!-- Layout Section 2: Chart + Modified modules -->
<div class="grid-2-1">
    <!-- Trend Chart -->
    <div class="card" style="padding: 24px;">
        <h2 class="widget-title">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M5 9.2h3V19H5zM10.5 5h3v14h-3zm5.5 8h3v6h-3z"/></svg>
            Daily Auditing & Interaction Trends
        </h2>
        <div class="chart-container" style="position: relative; height: 320px; width: 100%;">
            <canvas id="trendsChart"></canvas>
        </div>
    </div>

    <!-- Modified modules -->
    <div class="card">
        <h2 class="widget-title">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
            Top Modified Modules
        </h2>
        <div class="module-list">
            @php
                $maxModuleCount = $topModules->max('count') ?: 1;
            @endphp
            @forelse($topModules as $mod)
                <div class="module-item">
                    <div class="module-info">
                        <span class="module-name">{{ $mod->module }}</span>
                        <span class="module-count">{{ $mod->count }} changes</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ ($mod->count / $maxModuleCount) * 100 }}%"></div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 40px 0;">
                    No modules logged yet.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Layout Section 3: Event breakdowns + Top users -->
<div class="grid-2">
    <!-- Action Event Breakdown -->
    <div class="card">
        <h2 class="widget-title">Database Action Breakdown</h2>
        <div class="breakdown-list">
            <div class="breakdown-item">
                <div class="breakdown-label">CREATE</div>
                <div class="breakdown-val" style="color: var(--accent-create)">{{ number_format($createLogs) }}</div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label">UPDATE</div>
                <div class="breakdown-val" style="color: var(--accent-update)">{{ number_format($updateLogs) }}</div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label">DELETE</div>
                <div class="breakdown-val" style="color: var(--accent-delete)">{{ number_format($deleteLogs) }}</div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label">RESTORE</div>
                <div class="breakdown-val" style="color: #06b6d4">{{ number_format($restoreLogs) }}</div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label">APPROVE</div>
                <div class="breakdown-val" style="color: #0ea5e9">{{ number_format($approveLogs) }}</div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label">REJECT</div>
                <div class="breakdown-val" style="color: #64748b">{{ number_format($rejectLogs) }}</div>
            </div>
        </div>
    </div>

    <!-- Active users list -->
    <div class="card">
        <h2 class="widget-title">Top Active Users</h2>
        <div class="user-list">
            @forelse($topUsers as $topUser)
                <div class="user-item">
                    <div class="user-avatar">
                        {{ substr($topUser->user->name ?? 'G', 0, 2) }}
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ $topUser->user->name ?? 'Guest User' }}</div>
                        <div class="user-email">{{ $topUser->user->email ?? 'no-email@guest.com' }}</div>
                    </div>
                    <span class="user-count">{{ $topUser->count }} events</span>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 40px 0;">
                    No active user logs.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Layout Section 4: Timelines (Activities & Security Warnings) -->
<div class="grid-2">
    <!-- User interactions Timeline -->
    <div class="card">
        <h2 class="widget-title">Recent User Activities</h2>
        <div class="timeline-container">
            @forelse($recentActivities as $activity)
                <div class="timeline-item">
                    <div class="timeline-badge">
                        @if(str_contains(strtolower($activity->activity), 'login') && !str_contains(strtolower($activity->activity), 'failed'))
                            🔓
                        @elseif(str_contains(strtolower($activity->activity), 'logout'))
                            🔒
                        @elseif(str_contains(strtolower($activity->activity), 'failed'))
                            ❌
                        @else
                            🌐
                        @endif
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">{{ $activity->activity }}</div>
                        <div class="timeline-desc">{{ $activity->user->name ?? 'Guest' }} performed this interaction.</div>
                        <div class="timeline-meta">
                            <span>🕒 {{ $activity->created_at->diffForHumans() }}</span>
                            <span>💻 {{ $activity->ip_address }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 40px 0;">
                    No user activities logged yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Security Warnings -->
    <div class="card">
        <h2 class="widget-title">Recent Security Violations</h2>
        <div class="security-timeline">
            @forelse($recentSecurityLogs as $security)
                <a href="{{ url(config('auditify.route_prefix', 'auditify') . '/security-logs/' . $security->id) }}" class="security-item">
                    <div class="security-indicator indicator-{{ $security->severity }}"></div>
                    <div class="security-details">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span class="security-title">{{ $security->title }}</span>
                            @if(!$security->is_read)
                                <span class="badge" style="background-color: rgba(239, 68, 68, 0.12); color: #ef4444; font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 700; border: 1px solid rgba(239, 68, 68, 0.2);">Unresolved</span>
                            @endif
                        </div>
                        <div class="security-desc">{{ $security->description }}</div>
                        <div class="security-meta">
                            🕒 {{ $security->created_at->diffForHumans() }} | By: {{ $security->user->name ?? 'System' }}
                        </div>
                    </div>
                </a>
            @empty
                <div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 40px 0;">
                    No security violations detected. Excellent!
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('trendsChart').getContext('2d');
        
        const labels = {!! json_encode(array_keys($actionChartData->toArray())) !!};
        const actionData = {!! json_encode(array_values($actionChartData->toArray())) !!};
        const activityData = {!! json_encode(array_values($activityChartData->toArray())) !!};

        const getChartColors = (theme) => {
            const isDark = theme === 'dark';
            return {
                text: isDark ? '#9ca3af' : '#4b5563',
                grid: isDark ? 'rgba(255, 255, 255, 0.04)' : 'rgba(0, 0, 0, 0.05)',
                tooltipBg: isDark ? '#111827' : '#ffffff',
                tooltipBorder: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.08)'
            };
        };

        const theme = document.documentElement.getAttribute('data-theme') || 'dark';
        let colors = getChartColors(theme);

        // Gradient Fills
        const actionGradient = ctx.createLinearGradient(0, 0, 0, 300);
        actionGradient.addColorStop(0, 'rgba(14, 165, 233, 0.25)');
        actionGradient.addColorStop(1, 'rgba(14, 165, 233, 0)');
 
        const activityGradient = ctx.createLinearGradient(0, 0, 0, 300);
        activityGradient.addColorStop(0, 'rgba(59, 130, 246, 0.25)');
        activityGradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Database Action Logs',
                        data: actionData,
                        borderColor: '#0ea5e9',
                        backgroundColor: actionGradient,
                        fill: true,
                        tension: 0.38,
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#0ea5e9',
                        pointBorderColor: '#ffffff'
                    },
                    {
                        label: 'User Activity Logs',
                        data: activityData,
                        borderColor: '#3b82f6',
                        backgroundColor: activityGradient,
                        fill: true,
                        tension: 0.38,
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#ffffff'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: colors.text,
                            font: { family: 'Inter', size: 12, weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: colors.tooltipBg,
                        borderColor: colors.tooltipBorder,
                        borderWidth: 1,
                        titleColor: theme === 'dark' ? '#ffffff' : '#111827',
                        bodyColor: colors.text,
                        bodyFont: { family: 'Inter' },
                        titleFont: { family: 'Inter', weight: '700' },
                        padding: 12,
                        cornerRadius: 8,
                        boxPadding: 4
                    }
                },
                scales: {
                    x: {
                        grid: { color: colors.grid },
                        ticks: { color: colors.text, font: { family: 'Inter', size: 11 } }
                    },
                    y: {
                        grid: { color: colors.grid },
                        ticks: { color: colors.text, font: { family: 'Inter', size: 11 }, precision: 0 }
                    }
                }
            }
        });

        // Listen for the custom themeChanged event and update colors dynamically!
        window.addEventListener('themeChanged', function(e) {
            const newColors = getChartColors(e.detail);
            chart.options.plugins.legend.labels.color = newColors.text;
            chart.options.plugins.tooltip.backgroundColor = newColors.tooltipBg;
            chart.options.plugins.tooltip.borderColor = newColors.tooltipBorder;
            chart.options.plugins.tooltip.titleColor = e.detail === 'dark' ? '#ffffff' : '#111827';
            chart.options.plugins.tooltip.bodyColor = newColors.text;
            chart.options.scales.x.grid.color = newColors.grid;
            chart.options.scales.x.ticks.color = newColors.text;
            chart.options.scales.y.grid.color = newColors.grid;
            chart.options.scales.y.ticks.color = newColors.text;
            chart.update();
        });
    });
</script>
@endsection

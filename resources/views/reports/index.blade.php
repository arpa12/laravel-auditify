@extends('auditify::layout')

@section('title', 'Auditify Reports')
@section('header_title', 'Analytics & Reports')

@section('content')
<style>
    /* Timeframe Selector Styling */
    .filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .timeframe-select {
        background-color: var(--bg-secondary);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        outline: none;
        transition: var(--transition-smooth);
    }
    .timeframe-select:hover, .timeframe-select:focus {
        border-color: var(--border-hover);
        box-shadow: var(--shadow-sm);
    }

    /* Modern Report Tabs */
    .report-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 28px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 10px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .tab-btn {
        background: none;
        border: none;
        color: var(--text-secondary);
        padding: 10px 20px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        border-radius: 8px;
        white-space: nowrap;
        transition: var(--transition-smooth);
    }
    .tab-btn:hover {
        background-color: var(--nav-hover-bg);
        color: var(--text-primary);
    }
    .tab-btn.active {
        background-color: var(--color-indigo);
        color: white;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
    }

    /* Tab Content Grid Layouts */
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
        animation: tabFadeIn 0.35s ease-out forwards;
    }

    .grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-bottom: 28px;
    }

    .grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
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
        margin-bottom: 28px;
    }
    .card:hover {
        border-color: var(--border-hover);
    }

    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
        margin-top: 16px;
    }

    .widget-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--card-text-highlight);
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 12px;
        margin-bottom: 16px;
    }
    .widget-title svg {
        width: 16px;
        height: 16px;
        fill: currentColor;
    }

    /* KPI Mini Cards inside Tabs */
    .mini-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }
    .mini-kpi-card {
        background-color: var(--box-bg-light);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .mini-kpi-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .mini-kpi-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--card-text-highlight);
    }

    /* Tables in reports */
    .report-table-wrapper {
        width: 100%;
        overflow-x: auto;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background-color: var(--box-bg-light);
    }
    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }
    .report-table th {
        background-color: var(--table-header-bg);
        color: var(--text-secondary);
        font-weight: 600;
        text-align: left;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
    }
    .report-table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
    }
    .report-table tr:last-child td {
        border-bottom: none;
    }
    .report-table tr:hover td {
        background-color: var(--table-row-hover);
    }

    /* Severity badges */
    .severity-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .severity-critical { background-color: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    .severity-high { background-color: rgba(249, 115, 22, 0.12); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.2); }
    .severity-medium { background-color: rgba(234, 179, 8, 0.12); color: #eab308; border: 1px solid rgba(234, 179, 8, 0.2); }
    .severity-low { background-color: rgba(59, 130, 246, 0.12); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 700;
    }
    .status-resolved { background-color: rgba(16, 185, 129, 0.12); color: #10b981; }
    .status-pending { background-color: rgba(245, 158, 11, 0.12); color: #f59e0b; }

    @keyframes tabFadeIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 1024px) {
        .grid-3 { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .grid-2 { grid-template-columns: 1fr; }
        .grid-3 { grid-template-columns: 1fr; }
        .mini-kpi-grid { grid-template-columns: 1fr; }
    }

    .btn-danger {
        background-color: var(--accent-delete);
        color: white;
    }
    .btn-danger:hover {
        background-color: var(--accent-delete);
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
</style>

@php
    $prefix = trim(config('auditify.route_prefix', 'auditify'), '/');
@endphp

<div class="filter-bar">
    <div>
        <p style="color: var(--text-secondary); font-size: 0.85rem;">
            Analyzing metrics and activity distributions logged across the application.
        </p>
    </div>
    <form action="" method="GET" id="timeframeForm">
        <label for="timeframe" style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-right: 8px;">Timeframe:</label>
        <select name="timeframe" id="timeframe" class="timeframe-select" onchange="document.getElementById('timeframeForm').submit();">
            <option value="7" {{ $timeframe === '7' ? 'selected' : '' }}>Last 7 Days</option>
            <option value="30" {{ $timeframe === '30' ? 'selected' : '' }}>Last 30 Days</option>
            <option value="90" {{ $timeframe === '90' ? 'selected' : '' }}>Last 90 Days</option>
        </select>
    </form>
</div>

<!-- Navigation Tabs -->
<div class="report-tabs">
    <button class="tab-btn active" data-tab="overview">Overview Analytics</button>
    <button class="tab-btn" data-tab="action">Action Reports</button>
    <button class="tab-btn" data-tab="activity">Activity Reports</button>
    <button class="tab-btn" data-tab="security">Security Reports</button>
</div>

<!-- 1. OVERVIEW TAB -->
<div class="tab-content active" id="overview">
    <div class="mini-kpi-grid">
        <div class="mini-kpi-card" style="border-left: 4px solid var(--accent-update)">
            <span class="mini-kpi-label">Database Changes</span>
            <span class="mini-kpi-value">{{ number_format($totalActionLogs) }}</span>
        </div>
        <div class="mini-kpi-card" style="border-left: 4px solid #3b82f6">
            <span class="mini-kpi-label">User Activities</span>
            <span class="mini-kpi-value">{{ number_format($totalActivityLogs) }}</span>
        </div>
        <div class="mini-kpi-card" style="border-left: 4px solid #ef4444">
            <span class="mini-kpi-label">Security Alerts</span>
            <span class="mini-kpi-value">{{ number_format($totalSecurityLogs) }}</span>
        </div>
    </div>

    <div class="card" style="margin-bottom: 24px;">
        <h2 class="widget-title">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M5 9.2h3V19H5zM10.5 5h3v14h-3zm5.5 8h3v6h-3z"/></svg>
            System Log Activity Trends (Last {{ $days }} Days)
        </h2>
        <div class="chart-container" style="height: 340px;">
            <canvas id="overviewTrendChart"></canvas>
        </div>
    </div>
</div>

<!-- 2. ACTION REPORTS TAB -->
<div class="tab-content" id="action">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--card-text-highlight);">Database Modification Metrics</h3>
        <div style="display: flex; gap: 8px;">
            <a href="{{ url($prefix . '/action-logs/export/csv?start_date=' . $startDateString) }}" class="btn btn-secondary" style="font-size: 0.8rem; padding: 6px 12px;">
                📥 Export CSV
            </a>
            <a href="{{ url($prefix . '/action-logs/export/excel?start_date=' . $startDateString) }}" class="btn btn-primary" style="font-size: 0.8rem; padding: 6px 12px;">
                📊 Export Excel
            </a>
            <a href="{{ url($prefix . '/action-logs/export/pdf?start_date=' . $startDateString) }}" class="btn btn-danger" style="font-size: 0.8rem; padding: 6px 12px;">
                📕 Export PDF
            </a>
        </div>
    </div>

    <div class="grid-2">
        <!-- Action Breakdown Pie Chart -->
        <div class="card">
            <h2 class="widget-title">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 10V3c4.64.42 8.35 4.13 8.77 8.77H13zm-2 0H3c.42-4.64 4.13-8.35 8.77-8.77V12zm0 2v7.77c-4.64-.42-8.35-4.13-8.77-8.77H11zm2 0h7.77c-.42 4.64-4.13 8.35-8.77 8.77V14z"/></svg>
                Actions by Type
            </h2>
            @if(!empty($actionBreakdown))
                <div class="chart-container">
                    <canvas id="actionBreakdownChart"></canvas>
                </div>
            @else
                <div style="text-align: center; color: var(--text-muted); padding: 80px 0; font-size: 0.85rem;">
                    No actions recorded in this timeframe.
                </div>
            @endif
        </div>

        <!-- Top Modified Modules Bar Chart -->
        <div class="card">
            <h2 class="widget-title">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
                Most Changed Modules
            </h2>
            @if(!empty($actionsByModule))
                <div class="chart-container">
                    <canvas id="actionsByModuleChart"></canvas>
                </div>
            @else
                <div style="text-align: center; color: var(--text-muted); padding: 80px 0; font-size: 0.85rem;">
                    No module updates recorded in this timeframe.
                </div>
            @endif
        </div>
    </div>

    <!-- Action logs list table -->
    <div class="card">
        <h2 class="widget-title">Recent Modifications (Filtered Timeframe)</h2>
        @if($recentActionLogs->isNotEmpty())
            <div class="report-table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Description</th>
                            <th>Subject</th>
                            <th>Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActionLogs as $log)
                            <tr>
                                <td><strong>#{{ $log->id }}</strong></td>
                                <td>{{ $log->user->name ?? 'Guest User' }}</td>
                                <td>
                                    <span class="badge badge-{{ strtolower($log->action) === 'create' ? 'create' : (strtolower($log->action) === 'update' ? 'update' : (strtolower($log->action) === 'delete' ? 'delete' : 'other')) }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td><code>{{ $log->module }}</code></td>
                                <td>{{ $log->description }}</td>
                                <td>
                                    @if($log->subject_id && $log->subject_type)
                                        <span style="color: var(--text-secondary); font-size: 11px;">
                                            {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                        </span>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 11px;">-</span>
                                    @endif
                                </td>
                                <td style="color: var(--text-muted); font-size: 11px;">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; color: var(--text-muted); padding: 40px 0; font-size: 0.85rem;">
                No action trace data matching the filter.
            </div>
        @endif
    </div>
</div>

<!-- 3. ACTIVITY REPORTS TAB -->
<div class="tab-content" id="activity">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--card-text-highlight);">User Activity & Interaction Statistics</h3>
        <div style="display: flex; gap: 8px;">
            <a href="{{ url($prefix . '/activity-logs/export/csv?start_date=' . $startDateString) }}" class="btn btn-secondary" style="font-size: 0.8rem; padding: 6px 12px;">
                📥 Export CSV
            </a>
            <a href="{{ url($prefix . '/activity-logs/export/excel?start_date=' . $startDateString) }}" class="btn btn-primary" style="font-size: 0.8rem; padding: 6px 12px;">
                📊 Export Excel
            </a>
            <a href="{{ url($prefix . '/activity-logs/export/pdf?start_date=' . $startDateString) }}" class="btn btn-danger" style="font-size: 0.8rem; padding: 6px 12px;">
                📕 Export PDF
            </a>
        </div>
    </div>

    <div class="grid-2">
        <!-- Top Visited Pages Horizontal Bar -->
        <div class="card">
            <h2 class="widget-title">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.53c-.26-.81-1-1.4-1.9-1.4h-1v-3c0-.55-.45-1-1-1h-6v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.4z"/></svg>
                Top Visited Pages (URLs)
            </h2>
            @if(!empty($topPages))
                <div class="chart-container">
                    <canvas id="topPagesChart"></canvas>
                </div>
            @else
                <div style="text-align: center; color: var(--text-muted); padding: 80px 0; font-size: 0.85rem;">
                    No page visits logged in this timeframe.
                </div>
            @endif
        </div>

        <!-- Peak Activity Hours distribution -->
        <div class="card">
            <h2 class="widget-title">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                Hourly Peak Activity (24H Format)
            </h2>
            <div class="chart-container">
                <canvas id="hourlyActivityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Activity logs list table -->
    <div class="card">
        <h2 class="widget-title">Recent User Interactions (Filtered Timeframe)</h2>
        @if($recentActivityLogs->isNotEmpty())
            <div class="report-table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Activity</th>
                            <th>URL Path</th>
                            <th>IP Address</th>
                            <th>Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivityLogs as $log)
                            <tr>
                                <td><strong>#{{ $log->id }}</strong></td>
                                <td>{{ $log->user->name ?? 'Guest User' }}</td>
                                <td><code>{{ $log->activity }}</code></td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $log->url ? parse_url($log->url, PHP_URL_PATH) : '-' }}
                                </td>
                                <td><code>{{ $log->ip_address ?? '-' }}</code></td>
                                <td style="color: var(--text-muted); font-size: 11px;">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; color: var(--text-muted); padding: 40px 0; font-size: 0.85rem;">
                No user activities matching the filter.
            </div>
        @endif
    </div>
</div>

<!-- 4. SECURITY REPORTS TAB -->
<div class="tab-content" id="security">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--card-text-highlight);">Intrusion Alerts & Threat Reports</h3>
        <div style="display: flex; gap: 8px;">
            <a href="{{ url($prefix . '/security-logs/export/csv?start_date=' . $startDateString) }}" class="btn btn-secondary" style="font-size: 0.8rem; padding: 6px 12px;">
                📥 Export CSV
            </a>
            <a href="{{ url($prefix . '/security-logs/export/excel?start_date=' . $startDateString) }}" class="btn btn-primary" style="font-size: 0.8rem; padding: 6px 12px;">
                📊 Export Excel
            </a>
            <a href="{{ url($prefix . '/security-logs/export/pdf?start_date=' . $startDateString) }}" class="btn btn-danger" style="font-size: 0.8rem; padding: 6px 12px;">
                📕 Export PDF
            </a>
        </div>
    </div>

    <div class="grid-3">
        <!-- Severity Doughnut -->
        <div class="card">
            <h2 class="widget-title">Alerts by Severity</h2>
            @if(!empty($securitySeverity))
                <div class="chart-container" style="height: 250px;">
                    <canvas id="securitySeverityChart"></canvas>
                </div>
            @else
                <div style="text-align: center; color: var(--text-muted); padding: 60px 0; font-size: 0.85rem;">
                    No security alerts recorded.
                </div>
            @endif
        </div>

        <!-- Top IPs Bar -->
        <div class="card">
            <h2 class="widget-title">Top Threat Origins (IPs)</h2>
            @if(!empty($topSecurityIps))
                <div class="chart-container" style="height: 250px;">
                    <canvas id="securityIpsChart"></canvas>
                </div>
            @else
                <div style="text-align: center; color: var(--text-muted); padding: 60px 0; font-size: 0.85rem;">
                    No security threats logged.
                </div>
            @endif
        </div>

        <!-- Incident Status Breakdown -->
        <div class="card">
            <h2 class="widget-title">Resolution Statuses</h2>
            @if(!empty($resolutionStatus))
                <div class="chart-container" style="height: 250px;">
                    <canvas id="resolutionStatusChart"></canvas>
                </div>
            @else
                <div style="text-align: center; color: var(--text-muted); padding: 60px 0; font-size: 0.85rem;">
                    No incidents status reports available.
                </div>
            @endif
        </div>
    </div>

    <!-- Security logs list table -->
    <div class="card">
        <h2 class="widget-title">Recent Security Alerts (Filtered Timeframe)</h2>
        @if($recentSecurityLogs->isNotEmpty())
            <div class="report-table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Severity</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Origin IP</th>
                            <th>Status</th>
                            <th>Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSecurityLogs as $log)
                            <tr>
                                <td><strong>#{{ $log->id }}</strong></td>
                                <td>
                                    <span class="severity-badge severity-{{ strtolower($log->severity) }}">
                                        {{ $log->severity }}
                                    </span>
                                </td>
                                <td><strong>{{ $log->title }}</strong></td>
                                <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $log->description }}
                                </td>
                                <td><code>{{ $log->ip_address ?? 'System' }}</code></td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($log->status) === 'resolved' ? 'resolved' : 'pending' }}">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td style="color: var(--text-muted); font-size: 11px;">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; color: var(--text-muted); padding: 40px 0; font-size: 0.85rem;">
                No security incidents reported. Clean sheet!
            </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tab switching logic
        const tabButtons = document.querySelectorAll(".tab-btn");
        const tabContents = document.querySelectorAll(".tab-content");

        tabButtons.forEach(button => {
            button.addEventListener("click", () => {
                const targetTab = button.getAttribute("data-tab");

                tabButtons.forEach(btn => btn.classList.remove("active"));
                tabContents.forEach(content => content.classList.remove("active"));

                button.classList.add("active");
                document.getElementById(targetTab).classList.add("active");
            });
        });

        // Theme management for chart colors
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

        // Render Overview Trends Chart
        const trendsCtx = document.getElementById('overviewTrendChart').getContext('2d');
        const trendLabels = {!! json_encode(array_keys($actionChartData->toArray())) !!};
        const actionTrendData = {!! json_encode(array_values($actionChartData->toArray())) !!};
        const activityTrendData = {!! json_encode(array_values($activityChartData->toArray())) !!};
        const securityTrendData = {!! json_encode(array_values($securityChartData->toArray())) !!};

        const overviewTrendChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [
                    {
                        label: 'Database Action Logs',
                        data: actionTrendData,
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14, 165, 233, 0.05)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 2
                    },
                    {
                        label: 'User Activity Logs',
                        data: activityTrendData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.05)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 2
                    },
                    {
                        label: 'Security Alerts',
                        data: securityTrendData,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.05)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: colors.text, font: { family: 'Inter' } } }
                },
                scales: {
                    x: { grid: { color: colors.grid }, ticks: { color: colors.text } },
                    y: { grid: { color: colors.grid }, ticks: { color: colors.text, precision: 0 } }
                }
            }
        });

        // ACTION REPORTS CHARTS
        @if(!empty($actionBreakdown))
            const breakdownCtx = document.getElementById('actionBreakdownChart').getContext('2d');
            const breakdownData = {!! json_encode($actionBreakdown) !!};
            new Chart(breakdownCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(breakdownData),
                    datasets: [{
                        data: Object.values(breakdownData),
                        backgroundColor: ['#10b981', '#3b82f6', '#ef4444', '#06b6d4', '#f59e0b', '#64748b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { color: colors.text } }
                    }
                }
            });
        @endif

        @if(!empty($actionsByModule))
            const moduleCtx = document.getElementById('actionsByModuleChart').getContext('2d');
            const moduleData = {!! json_encode($actionsByModule) !!};
            new Chart(moduleCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(moduleData),
                    datasets: [{
                        label: 'Changes',
                        data: Object.values(moduleData),
                        backgroundColor: 'rgba(99, 102, 241, 0.75)',
                        borderWidth: 0,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { grid: { color: colors.grid }, ticks: { color: colors.text, precision: 0 } },
                        y: { grid: { display: false }, ticks: { color: colors.text } }
                    }
                }
            });
        @endif

        // ACTIVITY REPORTS CHARTS
        @if(!empty($topPages))
            const pagesCtx = document.getElementById('topPagesChart').getContext('2d');
            const pagesData = {!! json_encode($topPages) !!};
            new Chart(pagesCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(pagesData),
                    datasets: [{
                        label: 'Visits',
                        data: Object.values(pagesData),
                        backgroundColor: 'rgba(59, 130, 246, 0.75)',
                        borderWidth: 0,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { grid: { color: colors.grid }, ticks: { color: colors.text, precision: 0 } },
                        y: { grid: { display: false }, ticks: { color: colors.text } }
                    }
                }
            });
        @endif

        const hourlyCtx = document.getElementById('hourlyActivityChart').getContext('2d');
        const hourlyDistribution = {!! json_encode($hourlyDistribution) !!};
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(hourlyDistribution),
                datasets: [{
                    label: 'Logs Count',
                    data: Object.values(hourlyDistribution),
                    backgroundColor: 'rgba(14, 165, 233, 0.7)',
                    borderWidth: 0,
                    borderRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: colors.text } },
                    y: { grid: { color: colors.grid }, ticks: { color: colors.text, precision: 0 } }
                }
            }
        });

        // SECURITY REPORTS CHARTS
        @if(!empty($securitySeverity))
            const severityCtx = document.getElementById('securitySeverityChart').getContext('2d');
            const severityData = {!! json_encode($securitySeverity) !!};
            new Chart(severityCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(severityData).map(k => k.toUpperCase()),
                    datasets: [{
                        data: Object.values(severityData),
                        backgroundColor: ['#ef4444', '#f97316', '#eab308', '#3b82f6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: colors.text } }
                    }
                }
            });
        @endif

        @if(!empty($topSecurityIps))
            const ipsCtx = document.getElementById('securityIpsChart').getContext('2d');
            const ipsData = {!! json_encode($topSecurityIps) !!};
            new Chart(ipsCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(ipsData),
                    datasets: [{
                        label: 'Incidents',
                        data: Object.values(ipsData),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderWidth: 0,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: colors.text } },
                        y: { grid: { color: colors.grid }, ticks: { color: colors.text, precision: 0 } }
                    }
                }
            });
        @endif

        @if(!empty($resolutionStatus))
            const statusCtx = document.getElementById('resolutionStatusChart').getContext('2d');
            const statusData = {!! json_encode($resolutionStatus) !!};
            new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: Object.keys(statusData).map(s => s.replace('_', ' ').toUpperCase()),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: ['#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#64748b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: colors.text } }
                    }
                }
            });
        @endif

        // Theme switch listener to update charts dynamically
        window.addEventListener('themeChanged', function(e) {
            const newColors = getChartColors(e.detail);
            
            // Update trends chart
            overviewTrendChart.options.plugins.legend.labels.color = newColors.text;
            overviewTrendChart.options.scales.x.grid.color = newColors.grid;
            overviewTrendChart.options.scales.x.ticks.color = newColors.text;
            overviewTrendChart.options.scales.y.grid.color = newColors.grid;
            overviewTrendChart.options.scales.y.ticks.color = newColors.text;
            overviewTrendChart.update();
        });
    });
</script>
@endsection

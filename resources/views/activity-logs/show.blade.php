@extends('auditify::layout')

@section('title', 'Activity Log #' . $log->id . ' Details')
@section('header_title', 'Activity Log Details')

@section('header_actions')
    <a href="{{ url(config('auditify.route_prefix', 'auditify') . '/activity-logs') }}" class="btn btn-secondary">
        &larr; Back to Logs
    </a>
@endsection

@section('content')
<style>
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 28px;
        align-items: start;
        margin-bottom: 32px;
    }
    .meta-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .meta-item {
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 10px;
        font-size: 13px;
    }
    .meta-label {
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
    }
    .meta-val {
        color: var(--card-text-highlight);
        text-align: right;
        word-break: break-all;
    }
    
    /* Tabs Control */
    .tabs-header {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 24px;
        padding-bottom: 2px;
    }
    .tab-btn {
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: 14px;
        font-weight: 600;
        padding: 10px 18px;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: var(--transition-smooth);
    }
    .tab-btn:hover {
        color: var(--text-primary);
    }
    .tab-btn.active {
        color: var(--color-indigo);
        border-bottom-color: var(--color-indigo);
    }
    .tab-panel {
        display: none;
    }
    .tab-panel.active {
        display: block;
        animation: fadeIn 0.3s ease-out forwards;
    }

    /* Code Terminal View */
    .code-terminal {
        background-color: var(--code-terminal-bg, #030712);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 20px;
        font-family: var(--font-mono);
        color: var(--code-terminal-color, #34d399);
        font-size: 13px;
        line-height: 1.5;
        overflow-x: auto;
    }

    @media (max-width: 1024px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="detail-grid">
    <!-- Left Column: Log metadata summary -->
    <div class="card">
        <h2 class="widget-title">Log Metadata</h2>
        <div class="meta-list">
            <div class="meta-item">
                <span class="meta-label">Log ID</span>
                <span class="meta-val" style="font-family: var(--font-mono);">#{{ $log->id }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Responsible User</span>
                <span class="meta-val">{{ $log->user?->name ?? 'Guest' }}</span>
            </div>
            @php
                $emailKey = config('auditify.user_fields.email', 'email');
                $usernameKey = config('auditify.user_fields.username', 'username');
                $phoneKey = config('auditify.user_fields.phone', 'phone');
            @endphp
            <div class="meta-item">
                <span class="meta-label">User Email</span>
                <span class="meta-val">{{ $log->user?->{$emailKey} ?? 'N/A' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Username</span>
                <span class="meta-val">{{ $log->user?->{$usernameKey} ?? 'N/A' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Phone</span>
                <span class="meta-val">{{ $log->user?->{$phoneKey} ?? 'N/A' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">IP Address</span>
                <span class="meta-val" style="font-family: var(--font-mono);">{{ $log->ip_address ?? 'N/A' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Triggered At</span>
                <span class="meta-val"><span class="audit-timestamp" data-timestamp="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('Y-m-d H:i:s') }}</span></span>
            </div>
            <div class="meta-item" style="flex-direction: column; align-items: flex-start; gap: 8px;">
                <span class="meta-label">Request URL</span>
                <span class="meta-val" style="text-align: left; font-size: 12px; color: var(--text-secondary); width: 100%;">
                    {{ $log->url ?? 'N/A' }}
                </span>
            </div>
            <div class="meta-item" style="flex-direction: column; align-items: flex-start; gap: 8px; border-bottom: none;">
                <span class="meta-label">User Agent</span>
                <span class="meta-val" style="text-align: left; font-size: 11px; color: var(--text-muted); width: 100%;">
                    {{ $log->user_agent ?? 'N/A' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Right Column: Activity details & properties -->
    <div class="card" style="padding: 24px;">
        <div class="tabs-header">
            <button class="tab-btn active" onclick="switchTab(event, 'tab-details')">Activity Overview</button>
            <button class="tab-btn" onclick="switchTab(event, 'tab-json')">Raw JSON</button>
        </div>

        <!-- Activity Overview Panel -->
        <div id="tab-details" class="tab-panel active">
            <div style="margin-bottom: 24px;">
                <h3 style="font-size: 13px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                    Logged Activity
                </h3>
                @php
                    $badgeClass = 'act-other';
                    if (str_starts_with($log->activity, 'Login')) $badgeClass = 'act-login';
                    elseif (str_starts_with($log->activity, 'Logout')) $badgeClass = 'act-logout';
                    elseif (str_starts_with($log->activity, 'Failed Login')) $badgeClass = 'act-failed';
                    elseif (str_starts_with($log->activity, 'Page Visit')) $badgeClass = 'act-visit';
                @endphp
                <span class="activity-badge {{ $badgeClass }}" style="font-size: 14px; padding: 6px 12px; border-radius: 8px;">
                    {{ $log->activity }}
                </span>
            </div>

            @if(!empty($log->properties))
                <div>
                    <h3 style="font-size: 13px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
                        Custom Event Properties
                    </h3>
                    <pre class="code-terminal" style="background-color: var(--table-header-bg); border-color: var(--border-color); color: var(--text-primary);"><code>{!! json_encode($log->properties, JSON_PRETTY_PRINT) !!}</code></pre>
                </div>
            @else
                <div style="background-color: var(--box-bg-light); border: 1px dashed var(--border-color); border-radius: 8px; padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px;">
                    💡 No additional parameters or properties were attached to this user interaction.
                </div>
            @endif
        </div>

        <!-- Raw JSON Panel -->
        <div id="tab-json" class="tab-panel">
            <pre class="code-terminal"><code>{
  "id": {{ $log->id }},
  "user_id": {{ $log->user_id ?? 'null' }},
  "activity": "{{ $log->activity }}",
  "url": "{{ $log->url }}",
  "properties": {!! $log->properties ? json_encode($log->properties, JSON_PRETTY_PRINT) : 'null' !!},
  "ip_address": "{{ $log->ip_address }}",
  "user_agent": "{{ addslashes($log->user_agent) }}",
  "created_at": "{{ $log->created_at }}",
  "updated_at": "{{ $log->updated_at }}"
}</code></pre>
        </div>
    </div>
</div>

<script>
    function switchTab(e, tabId) {
        // Toggle active button class
        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(btn => btn.classList.remove('active'));
        e.target.classList.add('active');

        // Toggle active panel class
        const panels = document.querySelectorAll('.tab-panel');
        panels.forEach(p => p.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
    }
</script>
@endsection

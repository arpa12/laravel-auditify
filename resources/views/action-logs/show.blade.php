@extends('auditify::layout')

@section('title', 'Action Log #' . $log->id . ' Details')
@section('header_title', 'Action Log Details')

@section('header_actions')
    <a href="{{ url(config('auditify.route_prefix', 'auditify') . '/action-logs') }}" class="btn btn-secondary">
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

    /* Diff View Table */
    .diff-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        text-align: left;
    }
    .diff-table th {
        background-color: var(--table-header-bg);
        border-bottom: 1px solid var(--border-color);
        padding: 14px 18px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        text-align: left;
    }
    .diff-table td {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border-color);
    }
    .diff-table tr:last-child td {
        border-bottom: none;
    }
    
    .diff-row-modified { background-color: var(--diff-modified-bg); }
    .diff-row-added { background-color: var(--diff-added-bg); }
    .diff-row-removed { background-color: var(--diff-removed-bg); }

    .diff-removed-val {
        color: var(--diff-removed-color);
        text-decoration: line-through;
    }
    .diff-added-val {
        color: var(--diff-added-color);
        font-weight: 600;
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
                <span class="meta-label">Trigger User</span>
                <span class="meta-val">{{ $log->user?->name ?? 'Guest' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Action</span>
                <span class="meta-val">
                    @php
                        $badgeClass = 'badge-other';
                        if ($log->action === 'CREATE') $badgeClass = 'badge-create';
                        elseif ($log->action === 'UPDATE') $badgeClass = 'badge-update';
                        elseif ($log->action === 'DELETE') $badgeClass = 'badge-delete';
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $log->action }}</span>
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Module</span>
                <span class="meta-val" style="font-weight: 500;">{{ $log->module }}</span>
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

    <!-- Right Column: Modified values / visual diff / raw json -->
    <div class="card" style="padding: 24px;">
        <div class="tabs-header">
            <button class="tab-btn active" onclick="switchTab(event, 'tab-diff')">Visual Diff</button>
            <button class="tab-btn" onclick="switchTab(event, 'tab-json')">Raw JSON</button>
        </div>

        <!-- Visual Diff Tab Panel -->
        <div id="tab-diff" class="tab-panel active">
            @php
                $old = $log->old_values ?? [];
                $new = $log->new_values ?? [];
                $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
            @endphp

            @if(empty($allKeys))
                <div style="text-align: center; color: var(--text-muted); font-size: 13px; padding: 40px 0;">
                    No model change values were logged for this event (e.g. non-auditable or custom action).
                </div>
            @else
                <table class="diff-table">
                    <thead>
                        <tr>
                            <th>Attribute</th>
                            <th>Original Value</th>
                            <th>New Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allKeys as $key)
                            @php
                                $oldVal = $old[$key] ?? null;
                                $newVal = $new[$key] ?? null;

                                $hasOld = array_key_exists($key, $old);
                                $hasNew = array_key_exists($key, $new);

                                $isModified = ($hasOld && $hasNew && $oldVal !== $newVal);
                                $isAdded = (!$hasOld && $hasNew);
                                $isRemoved = ($hasOld && !$hasNew);

                                $rowClass = '';
                                if ($isModified) $rowClass = 'diff-row-modified';
                                elseif ($isAdded) $rowClass = 'diff-row-added';
                                elseif ($isRemoved) $rowClass = 'diff-row-removed';

                                // Normalize representation
                                $oldStr = is_array($oldVal) ? json_encode($oldVal) : (is_bool($oldVal) ? ($oldVal ? 'TRUE' : 'FALSE') : $oldVal);
                                $newStr = is_array($newVal) ? json_encode($newVal) : (is_bool($newVal) ? ($newVal ? 'TRUE' : 'FALSE') : $newVal);
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td style="font-family: var(--font-mono); font-weight: 500;">{{ $key }}</td>
                                <td>
                                    @if($isModified || $isRemoved)
                                        <span class="diff-removed-val">{{ $oldStr ?? 'NULL' }}</span>
                                    @elseif($isAdded)
                                        <span style="color: var(--text-muted); font-style: italic;">N/A</span>
                                    @else
                                        <span>{{ $oldStr ?? 'NULL' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isModified || $isAdded)
                                        <span class="diff-added-val">{{ $newStr ?? 'NULL' }}</span>
                                    @elseif($isRemoved)
                                        <span style="color: var(--text-muted); font-style: italic;">Deleted</span>
                                    @else
                                        <span>{{ $newStr ?? 'NULL' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Raw JSON Panel -->
        <div id="tab-json" class="tab-panel">
            <pre class="code-terminal"><code>{
  "id": {{ $log->id }},
  "user_id": {{ $log->user_id ?? 'null' }},
  "action": "{{ $log->action }}",
  "module": "{{ $log->module }}",
  "description": "{{ addslashes($log->description) }}",
  "old_values": {!! $log->old_values ? json_encode($log->old_values, JSON_PRETTY_PRINT) : 'null' !!},
  "new_values": {!! $log->new_values ? json_encode($log->new_values, JSON_PRETTY_PRINT) : 'null' !!},
  "ip_address": "{{ $log->ip_address }}",
  "url": "{{ $log->url }}",
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

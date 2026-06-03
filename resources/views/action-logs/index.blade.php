@extends('auditify::layout')

@section('title', 'Action Logs - Auditify')
@section('header_title', 'Action Logs')

@section('header_actions')
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) === request()->fullUrl() ? request()->fullUrl() : url(config('auditify.route_prefix', 'auditify') . '/action-logs/export/csv?' . http_build_query(request()->query())) }}" class="btn btn-secondary">
        📊 Export CSV
    </a>
    <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) === request()->fullUrl() ? request()->fullUrl() : url(config('auditify.route_prefix', 'auditify') . '/action-logs/export/excel?' . http_build_query(request()->query())) }}" class="btn btn-primary">
        📈 Export Excel
    </a>
@endsection

@section('content')
<style>
    .filter-panel {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .form-control {
        background-color: var(--bg-input);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-family: var(--font-sans);
        transition: var(--transition-smooth);
    }
    .form-control:focus {
        border-color: var(--color-indigo);
        outline: none;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }
    .filter-actions {
        display: flex;
        justify-content: flex-end;
        align-items: flex-end;
        gap: 12px;
        height: 100%;
    }
    
    .table-container {
        width: 100%;
        overflow-x: auto;
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
        background-color: var(--bg-secondary);
        margin-bottom: 24px;
    }
    .audit-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }
    .audit-table th {
        background-color: var(--table-header-bg);
        border-bottom: 1px solid var(--border-color);
        padding: 16px 20px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
    }
    .audit-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
    }
    .audit-table tr:hover td {
        background-color: var(--table-row-hover);
    }
    .audit-table tr:last-child td {
        border-bottom: none;
    }

    .user-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .user-avatar-small {
        width: 24px;
        height: 24px;
        background-color: var(--border-hover);
        color: var(--text-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 10px;
    }

    /* Paginator styling */
    .paginator-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--text-secondary);
        font-size: 13px;
    }
    .paginator-container a, .paginator-container span {
        padding: 8px 16px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        text-decoration: none;
        color: var(--text-primary);
        transition: var(--transition-smooth);
    }
    .paginator-container a:hover {
        border-color: var(--border-hover);
        background-color: var(--table-row-hover);
    }
    .paginator-container .active {
        background-color: var(--color-indigo);
        border-color: var(--color-indigo);
        color: white;
    }

    @media (max-width: 1024px) {
        .filter-panel {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 640px) {
        .filter-panel {
            grid-template-columns: 1fr;
        }
        .filter-actions {
            justify-content: stretch;
            width: 100%;
            margin-top: 8px;
        }
        .filter-actions .btn {
            flex-grow: 1;
        }
    }
</style>

<!-- Filters -->
<div class="card" style="margin-bottom: 32px;">
    <form method="GET" action="{{ url(config('auditify.route_prefix', 'auditify') . '/action-logs') }}">
        <div class="filter-panel">
            <div class="form-group">
                <label for="search">Keywords</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search description..." class="form-control">
            </div>

            <div class="form-group">
                <label for="action">Action Type</label>
                <select name="action" id="action" class="form-control">
                    <option value="">All Actions</option>
                    @foreach(['CREATE', 'UPDATE', 'DELETE', 'RESTORE', 'APPROVE', 'REJECT'] as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="module">Module</label>
                <select name="module" id="module" class="form-control">
                    <option value="">All Modules</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="user_id">User</label>
                <select name="user_id" id="user_id" class="form-control">
                    <option value="">All Users</option>
                    @foreach($users as $usr)
                        <option value="{{ $usr->id }}" {{ request('user_id') == $usr->id ? 'selected' : '' }}>{{ $usr->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="ip_address">IP Address</label>
                <input type="text" name="ip_address" id="ip_address" value="{{ request('ip_address') }}" placeholder="e.g. 197.10.12" class="form-control">
            </div>

            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control">
            </div>

            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-control">
            </div>

            <div class="filter-actions">
                <a href="{{ url(config('auditify.route_prefix', 'auditify') . '/action-logs') }}" class="btn btn-secondary">Clear Filters</a>
                <button type="submit" class="btn btn-primary">Filter Logs</button>
            </div>
        </div>
    </form>
</div>

<!-- Logs Grid -->
<div class="table-container">
    <table class="audit-table">
        <thead>
            <tr>
                <th style="width: 80px; white-space: nowrap;">ID</th>
                <th style="white-space: nowrap;">User</th>
                <th style="white-space: nowrap;">Action</th>
                <th style="white-space: nowrap;">Module</th>
                <th>Description</th>
                <th style="white-space: nowrap;">IP Address</th>
                <th style="white-space: nowrap;">Created At</th>
                <th style="width: 100px; text-align: center; white-space: nowrap;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="font-family: var(--font-mono); color: var(--text-secondary); white-space: nowrap;">#{{ $log->id }}</td>
                    <td style="white-space: nowrap;">
                        @if($log->user)
                            <div class="user-pill">
                                <div class="user-avatar-small">{{ substr($log->user->name, 0, 2) }}</div>
                                <span>{{ $log->user->name }}</span>
                            </div>
                        @else
                            <span style="color: var(--text-muted);">Guest</span>
                        @endif
                    </td>
                    <td style="white-space: nowrap;">
                        @php
                            $badgeClass = 'badge-other';
                            if ($log->action === 'CREATE') $badgeClass = 'badge-create';
                            elseif ($log->action === 'UPDATE') $badgeClass = 'badge-update';
                            elseif ($log->action === 'DELETE') $badgeClass = 'badge-delete';
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $log->action }}</span>
                    </td>
                    <td style="font-weight: 500; white-space: nowrap;">{{ $log->module }}</td>
                    <td style="color: var(--text-secondary);">{{ $log->description }}</td>
                    <td style="font-family: var(--font-mono); font-size: 12px; color: var(--text-secondary); white-space: nowrap;">{{ $log->ip_address ?? '-' }}</td>
                    <td style="white-space: nowrap;"><span class="audit-timestamp" data-timestamp="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('Y-m-d H:i:s') }}</span></td>
                    <td style="text-align: center; white-space: nowrap;">
                        <a href="{{ url(config('auditify.route_prefix', 'auditify') . '/action-logs/' . $log->id) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; border-radius: 6px;">
                            Details
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                        No database action logs found matching the filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination Links -->
@if($logs->hasPages())
    <div class="paginator-container">
        <div style="color: var(--text-secondary); font-size: 13px;">
            Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results
        </div>
        <div>
            {{ $logs->links('auditify::partials.pagination') }}
        </div>
    </div>
@endif

@endsection

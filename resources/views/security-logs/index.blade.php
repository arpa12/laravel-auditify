@extends('auditify::layout')

@section('title', 'Security Logs - Auditify')
@section('header_title', 'Security Logs')

@section('header_actions')
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) === request()->fullUrl() ? request()->fullUrl() : url(config('auditify.route_prefix', 'auditify') . '/security-logs/export/csv?' . http_build_query(request()->query())) }}" class="btn btn-secondary">
        📊 Export CSV
    </a>
    <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) === request()->fullUrl() ? request()->fullUrl() : url(config('auditify.route_prefix', 'auditify') . '/security-logs/export/excel?' . http_build_query(request()->query())) }}" class="btn btn-primary">
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
        grid-column: span 2;
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

    /* Severity badges styling */
    .sev-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .sev-critical { background-color: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
    .sev-high { background-color: rgba(249, 115, 22, 0.15); color: #f97316; border: 1px solid rgba(249, 115, 22, 0.3); }
    .sev-medium { background-color: rgba(234, 179, 8, 0.15); color: #eab308; border: 1px solid rgba(234, 179, 8, 0.3); }
    .sev-low { background-color: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); }

    /* Unread highlighting row */
    .row-unread {
        background-color: rgba(239, 68, 68, 0.01);
    }
    .row-unread td {
        font-weight: 500;
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
    @media (max-width: 1024px) {
        .filter-panel {
            grid-template-columns: repeat(2, 1fr);
        }
        .filter-actions {
            grid-column: span 2;
        }
    }
    @media (max-width: 640px) {
        .filter-panel {
            grid-template-columns: 1fr;
        }
        .filter-actions {
            grid-column: span 1;
            justify-content: stretch;
            width: 100%;
            margin-top: 8px;
        }
        .filter-actions .btn {
            flex-grow: 1;
        }
    }
</style>

@if(session('success'))
    <div style="background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; padding: 14px 20px; border-radius: 8px; margin-bottom: 24px; font-size: 13px;">
        {{ session('success') }}
    </div>
@endif

<!-- Filters -->
<div class="card" style="margin-bottom: 32px;">
    <form method="GET" action="{{ url(config('auditify.route_prefix', 'auditify') . '/security-logs') }}">
        <div class="filter-panel">
            <div class="form-group">
                <label for="search">Keywords</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search breaches..." class="form-control">
            </div>

            <div class="form-group">
                <label for="severity">Severity</label>
                <select name="severity" id="severity" class="form-control">
                    <option value="">All Severities</option>
                    @foreach(['low', 'medium', 'high', 'critical'] as $sev)
                        <option value="{{ $sev }}" {{ request('severity') === $sev ? 'selected' : '' }}>{{ strtoupper($sev) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="is_read">Status</label>
                <select name="is_read" id="is_read" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>Unread</option>
                    <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>Read</option>
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
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control">
            </div>

            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-control">
            </div>

            <div class="filter-actions">
                <a href="{{ url(config('auditify.route_prefix', 'auditify') . '/security-logs') }}" class="btn btn-secondary">Clear Filters</a>
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
                <th style="white-space: nowrap;">Severity</th>
                <th style="white-space: nowrap;">Title</th>
                <th>Description</th>
                <th style="white-space: nowrap;">IP Address</th>
                <th style="white-space: nowrap;">Created At</th>
                <th style="width: 180px; text-align: center; white-space: nowrap;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr class="{{ !$log->is_read ? 'row-unread' : '' }}">
                    <td style="font-family: var(--font-mono); color: var(--text-secondary); white-space: nowrap;">#{{ $log->id }}</td>
                    <td style="white-space: nowrap;">
                        @if($log->user)
                            <div class="user-pill">
                                <span style="color: var(--text-primary);">{{ $log->user->name }}</span>
                            </div>
                        @else
                            <span style="color: var(--text-muted);">System</span>
                        @endif
                    </td>
                    <td style="white-space: nowrap;">
                        <span class="sev-badge sev-{{ $log->severity }}">{{ $log->severity }}</span>
                    </td>
                    <td style="color: var(--card-text-highlight); white-space: nowrap;">{{ $log->title }}</td>
                    <td style="color: var(--text-secondary);">{{ $log->description }}</td>
                    <td style="font-family: var(--font-mono); color: var(--text-primary); white-space: nowrap;">{{ $log->ip_address ?? '-' }}</td>
                    <td style="white-space: nowrap;"><span class="audit-timestamp" data-timestamp="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('Y-m-d H:i:s') }}</span></td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                            <a href="{{ url(config('auditify.route_prefix', 'auditify') . '/security-logs/' . $log->id) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; border-radius: 6px;">
                                Details
                            </a>
                            @if(!$log->is_read)
                                <form method="POST" action="{{ url(config('auditify.route_prefix', 'auditify') . '/security-logs/' . $log->id . '/read') }}" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px; border-radius: 6px; background-color: #10b981; box-shadow: none;">
                                        Resolve
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                        No security logs found matching the filters.
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

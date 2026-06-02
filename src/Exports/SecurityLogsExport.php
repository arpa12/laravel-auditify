<?php

namespace Auditify\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SecurityLogsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query->with('user');
    }

    public function query()
    {
        return $this->query;
    }

    public function map($log): array
    {
        return [
            $log->id,
            $log->user?->name ?? 'Guest' . ($log->user_id ? ' (ID: ' . $log->user_id . ')' : ''),
            strtoupper($log->severity),
            $log->title,
            $log->description,
            $log->ip_address ?? '-',
            $log->user_agent ?? '-',
            $log->is_read ? 'Read' : 'Unread',
            $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'User',
            'Severity',
            'Title',
            'Description',
            'IP Address',
            'User Agent',
            'Status',
            'Created At',
        ];
    }
}

<?php

namespace Auditify\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ActivityLogsExport implements FromQuery, WithHeadings, WithMapping
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
            $log->activity,
            $log->url ?? '-',
            $log->ip_address ?? '-',
            $log->user_agent ?? '-',
            $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'User',
            'Activity',
            'Request URL',
            'IP Address',
            'User Agent',
            'Created At',
        ];
    }
}

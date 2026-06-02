<?php

namespace Auditify\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ActionLogsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    /**
     * Create a new export instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function __construct($query)
    {
        $this->query = $query->with('user');
    }

    /**
     * Prepare query for export.
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Map each row before exporting.
     *
     * @param  mixed  $log
     * @return array
     */
    public function map($log): array
    {
        return [
            $log->id,
            $log->user?->name ?? 'Guest' . ($log->user_id ? ' (ID: ' . $log->user_id . ')' : ''),
            $log->action,
            $log->module,
            $log->description,
            $log->ip_address ?? '-',
            $log->url ?? '-',
            $log->user_agent ?? '-',
            $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }

    /**
     * Define the headings for the export sheet.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'User',
            'Action',
            'Module',
            'Description',
            'IP Address',
            'Request URL',
            'User Agent',
            'Created At',
        ];
    }
}

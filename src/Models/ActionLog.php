<?php

namespace Auditify\Models;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    protected $table = 'audit_action_logs';

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    public function subject()
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }
}

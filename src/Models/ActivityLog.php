<?php

namespace Auditify\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'audit_activity_logs';

    protected $guarded = [];

    public function user()
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }
}

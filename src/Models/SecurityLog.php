<?php

namespace Auditify\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    protected $table = 'audit_security_logs';

    protected $guarded = [];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}

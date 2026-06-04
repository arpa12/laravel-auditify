<?php

namespace Auditify\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Auditify\Traits\Auditable;

class AuditablePost extends Model
{
    use Auditable;

    protected $guarded = [];
    protected $table = 'posts';
}

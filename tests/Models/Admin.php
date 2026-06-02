<?php

namespace Auditify\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $guarded = [];
    protected $table = 'admins';
}

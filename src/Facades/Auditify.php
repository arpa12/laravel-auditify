<?php

namespace Auditify\Facades;

use Illuminate\Support\Facades\Facade;

class Auditify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'auditify';
    }
}

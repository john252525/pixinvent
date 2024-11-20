<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class WhatsApiFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'whatsapi';
    }
}

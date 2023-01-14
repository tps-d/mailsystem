<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class NumberConversion extends Facade
{

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mailsystem.numberconversion';
    }
}

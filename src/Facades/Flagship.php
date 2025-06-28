<?php

namespace Flagship\Facades;

use Illuminate\Support\Facades\Facade;

class Flagship extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'flagship';
    }
}
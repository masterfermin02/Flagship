<?php

namespace Flagship\Blade;

use Flagship\Facades\Flagship;
use Illuminate\Support\Facades\Blade;

class FlagshipDirective
{
    public static function register()
    {
        Blade::if('flagship', function ($flag, $user = null) {
            return Flagship::isEnabled($flag, $user);
        });
    }
}

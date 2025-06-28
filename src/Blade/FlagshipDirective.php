<?php

namespace Flagship\Blade;

use Flagship\Facades\Flagship;
use Illuminate\Support\Facades\Blade;

class FlagshipDirective
{
    public static function register(): void
    {
        Blade::if('flagship', function ($flag, $user = null) {
            return Flagship::isEnabled($flag, $user);
        });

        Blade::if('feature', function ($feature) {
            return Flagship::isEnabled($feature);
        });

        Blade::if('featureForUser', function ($feature, $user = null) {
            return Flagship::isEnabledForUser($feature, $user);
        });
    }
}

<?php

namespace Tests\CustomEvaluetors;

class BetaUserEvaluator
{
    public function evaluate($user): bool
    {
        return $user->email === 'beta@example.com';
    }
}

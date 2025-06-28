<?php

namespace Flagship\Services;

use Flagship\Contracts\FlagshipInterface;

class FlagshipService implements FlagshipInterface
{
    public function isEnabled(string $flag, $user = null): bool
    {
       
    }

    public function enable(string $flag): void
    {
       
    }

    public function disable(string $flag): void
    {
        
    }

    public function toggle(string $flag): void
    {
        
    }

    public function create(string $flag, bool $enabled = false, array $rules = []): void
    {
        
    }

    public function delete(string $flag): void
    {
       
    }

    public function all(): array
    {

    }

    private function evaluateRules(string $rules, $user): bool
    {
        
    }

    private function clearCache(string $flag): void
    {
       
    }
}
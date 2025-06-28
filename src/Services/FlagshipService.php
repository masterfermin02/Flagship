<?php

namespace Flagship\Services;

use Flagship\Contracts\FlagshipInterface;
use Flagship\Models\FeatureFlag;
use Illuminate\Support\Facades\Cache;

class FlagshipService implements FlagshipInterface
{
    public function isEnabled(string $flag, $user = null): bool
    {
        $cacheKey = "flagship.{$flag}";

        if (config('flagship.cache_enabled', true)) {
            $featureFlag = Cache::remember($cacheKey, config('flagship.cache_ttl', 3600), function () use ($flag) {
                return FeatureFlag::where('name', $flag)->first();
            });
        } else {
            $featureFlag = FeatureFlag::where('name', $flag)->first();
        }

        if (!$featureFlag) {
            return config('flagship.default_state', false);
        }

        if (!$featureFlag->is_active) {
            return false;
        }

        if ($featureFlag->rules && $user) {
            // WIP: evaluate rules
            // return $this->evaluateRules($featureFlag->rules, $user);
        }

        return true;
    }

    public function enable(string $flag): void
    {
        $featureFlag = FeatureFlag::where('name', $flag)->first();

        if ($featureFlag) {
            $featureFlag->update(['is_active' => true]);
            $this->clearCache($flag);
        }
    }

    public function disable(string $flag): void
    {
        $featureFlag = FeatureFlag::where('name', $flag)->first();

        if ($featureFlag) {
            $featureFlag->update(['is_active' => false]);
            $this->clearCache($flag);
        }
    }

    public function toggle(string $flag): void
    {
        $featureFlag = FeatureFlag::where('name', $flag)->first();

        if ($featureFlag) {
            $featureFlag->update(['is_active' => !$featureFlag->is_active]);
            $this->clearCache($flag);
        }
    }

    public function create(string $flag, bool $enabled = null, array $rules = []): void
    {
        if ($enabled === null) {
            $enabled = config('flagship.default_state', false);
        }

        FeatureFlag::updateOrCreate(
            ['name' => $flag],
            [
                'is_active' => $enabled,
                'rules' => empty($rules) ? null : json_encode($rules),
                'description' => "Feature flag: {$flag}"
            ]
        );

        $this->clearCache($flag);
    }

    public function delete(string $flag): void
    {
        FeatureFlag::where('name', $flag)->delete();
        $this->clearCache($flag);
    }

    public function all(): array
    {
        return FeatureFlag::all()->toArray();
    }

    // WIP: evaluate rules
    // private function evaluateRules(string $rules, $user): bool
    // {
    // }

    private function clearCache(string $flag): void
    {
        if (config('flagship.cache_enabled', true)) {
            Cache::forget("flagship.{$flag}");
        }
    }
}

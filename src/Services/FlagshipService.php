<?php

namespace Flagship\Services;

use Carbon\Carbon;
use Flagship\Contracts\FlagshipInterface;
use Flagship\Contracts\TrackAbleUser;
use Flagship\Models\FeatureEvent;
use Flagship\Models\FeatureFlag;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Container\Container;

class FlagshipService implements FlagshipInterface
{
    protected $evaluator;
    public function __construct(public readonly Container $container)
    {
        $this->evaluator = $this->resolveEvaluator(config('flagship.evaluator'));
    }

    protected function resolveEvaluator(mixed $config): callable
    {
        if (is_callable($config)) {
            return $config;
        }

        if (is_string($config) && class_exists($config)) {
            $resolved = $this->container->make($config);

            if (is_callable($resolved)) {
                return $resolved;
            }
        }

        // Fallback: always return true
        return fn(array $rules, $user) => true;
    }

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

        if (is_array($featureFlag->environments)) {
            $env = App::environment();
            if (isset($featureFlag->environments[$env]) && $featureFlag->environments[$env] === false) {
                return false;
            }
        }

        if (!$featureFlag->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($featureFlag->scheduled_start && $now->lt($featureFlag->scheduled_start)) {
            return false;
        }

        if ($featureFlag->scheduled_end && $now->gt($featureFlag->scheduled_end)) {
            return false;
        }

        if ($featureFlag->rules && $user) {
            return $this->evaluateRules($featureFlag->rules, $user);
        }

        return true;
    }

    private function evaluateRules(array $rules, $user): bool
    {
        return call_user_func($this->evaluator, $rules, $user);
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

    public function getVariant(string $featureName, $user)
    {
        $feature = FeatureFlag::where('name', $featureName)->first();

        if (! $feature || !is_array($feature->variants)) {
            return null;
        }

        $userId = is_object($user) ? $user->id : (string) $user;

        // Deterministic hash (e.g. crc32 or md5)
        $hash = crc32($featureName . $userId); // Stable for same inputs
        $percentage = $hash % 100; // Get a value between 0-99

        $cumulative = 0;

        foreach ($feature->variants as $name => $data) {
            $weight = $data['weight'] ?? 0;
            $cumulative += $weight;

            if ($percentage < $cumulative) {
                return $name;
            }
        }

        return null;
    }

    public function track(string $featureName,TrackAbleUser $user, string $eventType, array $metadata = []): void
    {
        $userId = $user->getId();

        FeatureEvent::create([
            'feature_name' => $featureName,
            'user_id' => $userId,
            'event_type' => $eventType,
            'metadata' => $metadata,
        ]);
    }

    private function clearCache(string $flag): void
    {
        if (config('flagship.cache_enabled', true)) {
            Cache::forget("flagship.{$flag}");
        }
    }

    public function getFeatureStats(string $featureName): array
    {
        // Count impressions (viewed events)
        $impressions = FeatureEvent::where('feature_name', $featureName)
            ->where('event_type', 'viewed')
            ->count();

        // Count interactions (all events except viewed)
        $interactions = FeatureEvent::where('feature_name', $featureName)
            ->where('event_type', '!=', 'viewed')
            ->count();

        // Calculate conversion rate
        $conversionRate = $impressions > 0 ? round(($interactions / $impressions) * 100, 2) : 0;

        return [
            'impressions' => $impressions,
            'interactions' => $interactions,
            'conversion_rate' => $conversionRate . '%'
        ];
    }

    public function getABTestResults(string $testName): array
    {
        // Get the feature flag to check if it has variants
        $featureFlag = FeatureFlag::where('name', $testName)->first();

        if (!$featureFlag || !is_array($featureFlag->variants) || empty($featureFlag->variants)) {
            return ['error' => 'No A/B test found with this name or no variants defined'];
        }

        $results = [];
        $variantNames = array_keys($featureFlag->variants);

        // Initialize results for each variant
        foreach ($variantNames as $variant) {
            $results[$variant] = [
                'impressions' => 0,
                'interactions' => 0,
                'conversion_rate' => '0%'
            ];
        }

        // Get all events for this test
        $events = FeatureEvent::where('feature_name', $testName)->get();

        // Group events by user
        $userEvents = [];
        foreach ($events as $event) {
            if (!isset($userEvents[$event->user_id])) {
                $userEvents[$event->user_id] = [];
            }
            $userEvents[$event->user_id][] = $event;
        }

        // Process events by user
        foreach ($userEvents as $userId => $userEventList) {
            // Determine which variant this user saw
            $variant = $this->getVariant($testName, $userId);

            // If no variant determined, skip this user
            if (!$variant || !in_array($variant, $variantNames)) {
                continue;
            }

            // Count impressions and interactions for this user
            $hasImpression = false;
            $hasInteraction = false;

            foreach ($userEventList as $event) {
                if ($event->event_type === 'viewed') {
                    $hasImpression = true;
                } else {
                    $hasInteraction = true;
                }
            }

            if ($hasImpression) {
                $results[$variant]['impressions']++;
            }

            if ($hasInteraction) {
                $results[$variant]['interactions']++;
            }
        }

        // Calculate conversion rates
        foreach ($results as $variant => $stats) {
            if ($stats['impressions'] > 0) {
                $conversionRate = round(($stats['interactions'] / $stats['impressions']) * 100, 2);
                $results[$variant]['conversion_rate'] = $conversionRate . '%';
            }
        }

        return $results;
    }
}

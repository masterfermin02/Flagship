<?php

namespace Flagship\Contracts;

interface FlagshipInterface
{
    public function isEnabled(string $flag, $user = null): bool;
    public function enable(string $flag): void;
    public function disable(string $flag): void;
    public function toggle(string $flag): void;
    public function create(string $flag, bool $enabled = false, array $rules = []): void;
    public function delete(string $flag): void;
    public function all(): array;
    public function track(string $featureName, $user, string $eventType, array $metadata = []): void;
    public function getFeatureStats(string $featureName): array;
    public function getABTestResults(string $testName): array;
}

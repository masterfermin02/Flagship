<?php

namespace Tests\Feature\Facades;

use Flagship\Facades\Flagship;
use Workbench\App\Models\User;

it('checks if a feature is enabled', function () {
    Flagship::shouldReceive('isEnabled')
        ->with('new-checkout')
        ->once()
        ->andReturn(true);

    $result = Flagship::isEnabled('new-checkout');

    expect($result)->toBeTrue();
});

it('checks if a feature is enabled for a user', function () {
    $user = User::factory()->make();

    Flagship::shouldReceive('isEnabledForUser')
        ->with('beta-dashboard', $user)
        ->once()
        ->andReturn(true);

    $result = Flagship::isEnabledForUser('beta-dashboard', $user);

    expect($result)->toBeTrue();
});

it('returns fallback value when feature is disabled', function () {
    Flagship::shouldReceive('isEnabled')
        ->with('experimental-ui', false)
        ->once()
        ->andReturn(false);

    $result = Flagship::isEnabled('experimental-ui', false);

    expect($result)->toBeFalse();
});

it('enables a feature globally', function () {
    Flagship::shouldReceive('isEnabled')
        ->with('new-checkout')
        ->once()
        ->andReturn(true);

    expect(Flagship::isEnabled('new-checkout'))->toBeTrue();
});

it('disables a feature globally', function () {
    Flagship::shouldReceive('isEnabled')
        ->with('legacy-flow')
        ->once()
        ->andReturn(false);

    expect(Flagship::isEnabled('legacy-flow'))->toBeFalse();
});

it('enables a feature for a specific user', function () {
    $user = User::factory()->make();

    Flagship::shouldReceive('isEnabledForUser')
        ->with('beta-dashboard', $user)
        ->once()
        ->andReturn(true);

    expect(Flagship::isEnabledForUser('beta-dashboard', $user))->toBeTrue();
});

it('disables a feature for a specific user', function () {
    $user = User::factory()->make();

    Flagship::shouldReceive('isEnabledForUser')
        ->with('admin-tools', $user)
        ->once()
        ->andReturn(false);

    expect(Flagship::isEnabledForUser('admin-tools', $user))->toBeFalse();
});

it('returns fallback value for missing feature flag', function () {
    Flagship::shouldReceive('isEnabled')
        ->with('non-existent-flag', false)
        ->once()
        ->andReturn(false);

    expect(Flagship::isEnabled('non-existent-flag', false))->toBeFalse();
});

it('overrides global flag with user-specific flag', function () {
    $user = User::factory()->make();

    Flagship::shouldReceive('isEnabled')->with('new-dashboard')->andReturn(false);
    Flagship::shouldReceive('isEnabledForUser')->with('new-dashboard', $user)->andReturn(true);

    expect(Flagship::isEnabledForUser('new-dashboard', $user))->toBeTrue();
});

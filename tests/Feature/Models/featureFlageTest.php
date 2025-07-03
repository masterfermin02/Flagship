<?php

use Flagship\Models\FeatureFlag as Feature;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CustomEvaluetors\BetaUserEvaluator;
use Workbench\App\Models\User;
use Flagship\Facades\Flagship;
use Carbon\Carbon;

uses(RefreshDatabase::class); // Ensure the DB is fresh for each test

it('creates a feature with correct attributes', function () {
    $feature = Feature::create([
        'name' => 'new-checkout',
        'description' => 'New streamlined checkout process',
        'is_active' => true,
        'rollout_percentage' => 25,
    ]);

    expect($feature)
        ->toBeInstanceOf(Feature::class)
        ->name->toBe('new-checkout')
        ->description->toBe('New streamlined checkout process')
        ->is_active->toBeTrue()
        ->rollout_percentage->toBe(25);

    expect(Feature::where('name', 'new-checkout')->exists())->toBeTrue();
});

it('creates a feature with user attribute targeting rules', function () {
    $feature = Feature::create([
        'name' => 'premium-features',
        'targeting_rules' => [
            'user_type' => 'premium',
            'registration_date' => ['after' => '2024-01-01'],
        ],
    ]);

    expect($feature)
        ->name->toBe('premium-features')
        ->targeting_rules->toBeArray();
    expect($feature->targeting_rules['user_type'])->toBe('premium');
    expect($feature->targeting_rules['registration_date']['after'])->toBe('2024-01-01');
});

it('creates a feature using a custom evaluator strategy', function () {
    $feature = Feature::create([
        'name' => 'beta-ui',
        'targeting_strategy' => 'custom',
        'custom_evaluator' => BetaUserEvaluator::class,
    ]);

    expect($feature)
        ->name->toBe('beta-ui')
        ->targeting_strategy->toBe('custom')
        ->custom_evaluator->toBe(BetaUserEvaluator::class);
});

it('creates a feature with weighted variants', function () {
    $feature = Feature::create([
        'name' => 'checkout-flow',
        'variants' => [
            'control'    => ['weight' => 50],
            'variant_a'  => ['weight' => 25],
            'variant_b'  => ['weight' => 25],
        ],
    ]);

    expect($feature->variants)->toBeArray()
        ->toHaveKeys(['control', 'variant_a', 'variant_b'])
        ->and($feature->variants['control']['weight'])->toBe(50);
});

it('returns the correct variant for a user', function () {
    $user = User::factory()->make(['id' => 123]);

    Flagship::shouldReceive('getVariant')
        ->with('checkout-flow', $user)
        ->once()
        ->andReturn('variant_a');

    $variant = Flagship::getVariant('checkout-flow', $user);

    expect($variant)->toBe('variant_a');
});

it('returns a valid variant from the variants list', function () {
    Feature::create([
        'name' => 'checkout-flow',
        'variants' => [
            'control'    => ['weight' => 50],
            'variant_a'  => ['weight' => 25],
            'variant_b'  => ['weight' => 25],
        ],
    ]);

    $user = User::factory()->make(['id' => 42]);

    $variant = Flagship::getVariant('checkout-flow', $user);

    expect(in_array($variant, ['control', 'variant_a', 'variant_b']))->toBeTrue();
});

it('only enables feature within scheduled window', function () {
    $now = Carbon::parse('2024-11-30 12:00:00'); // Black Friday active

    Carbon::setTestNow($now);

    Feature::create([
        'name' => 'black-friday-sale',
        'is_active' => true,
        'scheduled_start' => '2024-11-29 00:00:00',
        'scheduled_end' => '2024-12-02 23:59:59',
    ]);

    expect(Flagship::isEnabled('black-friday-sale'))->toBeTrue();

    // Before the start
    Carbon::setTestNow('2024-11-28 23:59:59');
    expect(Flagship::isEnabled('black-friday-sale'))->toBeFalse();

    // After the end
    Carbon::setTestNow('2024-12-03 00:00:00');
    expect(Flagship::isEnabled('black-friday-sale'))->toBeFalse();
});

it('enables or disables features based on environment local', function () {
    Feature::create([
        'name' => 'debug-toolbar',
        'is_active' => true,
        'environments' => [
            'local' => true,
            'staging' => true,
            'production' => false,
        ],
    ]);

    App::shouldReceive('environment')->andReturn('local');
    expect(Flagship::isEnabled('debug-toolbar'))->toBeTrue();
});

it('enables or disables features based on environment production', function () {
    Feature::create([
        'name' => 'debug-toolbar',
        'is_active' => true,
        'environments' => [
            'local' => true,
            'staging' => true,
            'production' => false,
        ],
    ]);

    App::shouldReceive('environment')->andReturn('production');
    expect(Flagship::isEnabled('debug-toolbar'))->toBeFalse();
});

it('enables or disables features based on environment staging', function () {
    Feature::create([
        'name' => 'debug-toolbar',
        'is_active' => true,
        'environments' => [
            'local' => true,
            'staging' => true,
            'production' => false,
        ],
    ]);

    App::shouldReceive('environment')->andReturn('staging');
    // Not explicitly set, should fallback to enabled
    expect(Flagship::isEnabled('debug-toolbar'))->toBeTrue();
});

it('tracks a feature impression', function () {
    $user = User::factory()->create();

    Flagship::track('new-checkout', $user, 'viewed');

    $this->assertDatabaseHas('feature_events', [
        'feature_name' => 'new-checkout',
        'user_id' => $user->id,
        'event_type' => 'viewed',
    ]);
});

it('tracks a feature interaction with metadata', function () {
    $user = User::factory()->create();

    Flagship::track('new-checkout', $user, 'completed_purchase', [
        'amount' => 99.99,
        'items' => 3
    ]);

    $this->assertDatabaseHas('feature_events', [
        'feature_name' => 'new-checkout',
        'user_id' => $user->id,
        'event_type' => 'completed_purchase',
    ]);

    $this->assertDatabaseCount('feature_events', 1);

    $event = \Illuminate\Support\Facades\DB::table('feature_events')->first();
    expect(json_decode($event->metadata, true))->toMatchArray([
        'amount' => 99.99,
        'items' => 3
    ]);
});

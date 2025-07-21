<?php

use Flagship\Services\FlagshipService;
use Flagship\Models\FeatureEvent;
use Flagship\Models\FeatureFlag;
use Flagship\Contracts\TrackAbleUser;
use Flagship\Facades\Flagship;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a test feature flag
    FeatureFlag::create([
        'name' => 'new-checkout',
        'is_active' => true,
        'description' => 'New checkout flow'
    ]);

    // Create a test A/B test with variants
    FeatureFlag::create([
        'name' => 'checkout-flow',
        'is_active' => true,
        'description' => 'A/B test for checkout flow',
        'variants' => [
            'control' => ['weight' => 50],
            'variant-a' => ['weight' => 50]
        ]
    ]);
});

// Helper function to create a TrackAbleUser with a specific ID
function createTrackableUser($id) {
    return new class($id) implements TrackAbleUser {
        private $id;

        public function __construct($id) {
            $this->id = $id;
        }

        public function getId(): mixed {
            return $this->id;
        }
    };
}

it('can get feature stats', function () {
    // Arrange
    $service = app(FlagshipService::class);

    // Create test users
    $users = [];
    for ($i = 1; $i <= 10; $i++) {
        $users[] = createTrackableUser($i);
    }

    // Track impressions for all users
    foreach ($users as $user) {
        $service->track('new-checkout', $user, 'viewed');
    }

    // Track interactions for some users
    for ($i = 0; $i < 5; $i++) {
        $service->track('new-checkout', $users[$i], 'clicked');
    }

    // Track completed purchases for fewer users
    for ($i = 0; $i < 2; $i++) {
        $service->track('new-checkout', $users[$i], 'completed_purchase');
    }

    // Act
    $stats = $service->getFeatureStats('new-checkout');

    // Assert
    expect($stats)->toBeArray();
    expect($stats['impressions'])->toBe(10);
    expect($stats['interactions'])->toBe(7); // 5 clicks + 2 purchases
    expect($stats['conversion_rate'])->toBe('70%'); // (7/10) * 100
});

it('can get feature stats via facade', function () {
    // Arrange
    // Create test users and track events
    for ($i = 1; $i <= 5; $i++) {
        Flagship::track('new-checkout', createTrackableUser($i), 'viewed');
    }

    for ($i = 1; $i <= 2; $i++) {
        Flagship::track('new-checkout', createTrackableUser($i), 'clicked');
    }

    // Act
    $stats = Flagship::getFeatureStats('new-checkout');

    // Assert
    expect($stats)->toBeArray();
    expect($stats['impressions'])->toBe(5);
    expect($stats['interactions'])->toBe(2);
    expect($stats['conversion_rate'])->toBe('40%'); // (2/5) * 100
});

it('returns zero conversion rate when no impressions', function () {
    // Arrange
    $service = app(FlagshipService::class);

    // Act
    $stats = $service->getFeatureStats('non-existent-feature');

    // Assert
    expect($stats)->toBeArray();
    expect($stats['impressions'])->toBe(0);
    expect($stats['interactions'])->toBe(0);
    expect($stats['conversion_rate'])->toBe('0%');
});

it('can get A/B test results', function () {
    // Arrange
    $service = app(FlagshipService::class);

    // Mock the getVariant method to return predictable results
    // First 5 users get 'control', next 5 get 'variant-a'
    $service = $this->partialMock(FlagshipService::class, function ($mock) {
        $mock->shouldReceive('getVariant')
            ->andReturnUsing(function ($testName, $userId) {
                if ($userId <= 5) {
                    return 'control';
                } else {
                    return 'variant-a';
                }
            });
    });

    // Create test events
    // Control group: 5 users, all view, 2 interact
    for ($i = 1; $i <= 5; $i++) {
        $service->track('checkout-flow', createTrackableUser($i), 'viewed');
    }
    for ($i = 1; $i <= 2; $i++) {
        $service->track('checkout-flow', createTrackableUser($i), 'clicked');
    }

    // Variant A: 5 users, all view, 3 interact
    for ($i = 6; $i <= 10; $i++) {
        $service->track('checkout-flow', createTrackableUser($i), 'viewed');
    }
    for ($i = 6; $i <= 8; $i++) {
        $service->track('checkout-flow', createTrackableUser($i), 'clicked');
    }

    // Act
    $results = $service->getABTestResults('checkout-flow');

    // Assert
    expect($results)->toBeArray();
    expect($results)->toHaveKeys(['control', 'variant-a']);

    // Control group: 5 impressions, 2 interactions, 40% conversion
    expect($results['control']['impressions'])->toBe(5);
    expect($results['control']['interactions'])->toBe(2);
    expect($results['control']['conversion_rate'])->toBe('40%');

    // Variant A: 5 impressions, 3 interactions, 60% conversion
    expect($results['variant-a']['impressions'])->toBe(5);
    expect($results['variant-a']['interactions'])->toBe(3);
    expect($results['variant-a']['conversion_rate'])->toBe('60%');
});

it('can get A/B test results via facade', function () {
    // Arrange
    // Mock the getVariant method in the service
    $service = $this->partialMock(FlagshipService::class, function ($mock) {
        $mock->shouldReceive('getVariant')
            ->andReturnUsing(function ($testName, $userId) {
                return $userId <= 3 ? 'control' : 'variant-a';
            });
    });

    // Bind the mocked service to the container
    app()->instance(FlagshipService::class, $service);

    // Create test events
    // Control group: 3 users
    for ($i = 1; $i <= 3; $i++) {
        Flagship::track('checkout-flow', createTrackableUser($i), 'viewed');
    }
    Flagship::track('checkout-flow', createTrackableUser(1), 'clicked');

    // Variant A: 3 users
    for ($i = 4; $i <= 6; $i++) {
        Flagship::track('checkout-flow', createTrackableUser($i), 'viewed');
    }
    Flagship::track('checkout-flow', createTrackableUser(4), 'clicked');
    Flagship::track('checkout-flow', createTrackableUser(5), 'clicked');

    // Act
    $results = Flagship::getABTestResults('checkout-flow');

    // Assert
    expect($results)->toBeArray();
    expect($results['control']['conversion_rate'])->toBe('33.33%');
    expect($results['variant-a']['conversion_rate'])->toBe('66.67%');
});

it('returns error for non-existent A/B test', function () {
    // Arrange
    $service = app(FlagshipService::class);

    // Act
    $results = $service->getABTestResults('non-existent-test');

    // Assert
    expect($results)->toBeArray();
    expect($results)->toHaveKey('error');
});

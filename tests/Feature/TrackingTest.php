<?php

use Flagship\Services\FlagshipService;
use Flagship\Models\FeatureEvent;
use Flagship\Contracts\TrackAbleUser;
use Flagship\Facades\Flagship;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can track feature impressions without metadata', function () {
    // Arrange
    $service = app(FlagshipService::class);
    $user = new class implements TrackAbleUser {
        public function getId(): mixed {
            return 123;
        }
    };

    // Act
    $service->track('new-checkout', $user, 'viewed');

    // Assert
    $this->assertDatabaseHas('feature_events', [
        'feature_name' => 'new-checkout',
        'user_id' => 123,
        'event_type' => 'viewed',
    ]);
});

it('can track feature interactions with metadata', function () {
    // Arrange
    $service = app(FlagshipService::class);
    $user = new class implements TrackAbleUser {
        public function getId(): mixed {
            return 456;
        }
    };
    $metadata = [
        'amount' => 99.99,
        'items' => 3
    ];

    // Act
    $service->track('new-checkout', $user, 'completed_purchase', $metadata);

    // Assert
    $event = FeatureEvent::where('feature_name', 'new-checkout')
        ->where('user_id', 456)
        ->where('event_type', 'completed_purchase')
        ->first();

    expect($event)->not->toBeNull();
    expect($event->metadata)->toBe($metadata);
});

it('can track via the Flagship facade', function () {
    // This test requires the Flagship facade to be properly set up
    // Arrange
    $user = new class implements TrackAbleUser {
        public function getId(): mixed {
            return 505;
        }
    };

    // Act
    Flagship::track('new-checkout', $user, 'viewed');

    // Assert
    $this->assertDatabaseHas('feature_events', [
        'feature_name' => 'new-checkout',
        'user_id' => 505,
        'event_type' => 'viewed',
    ]);
});

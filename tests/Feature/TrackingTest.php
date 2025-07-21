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

it('can track with a standard Laravel user model', function () {
    // Arrange
    $service = app(FlagshipService::class);
    $user = new class {
        public $id = 789;
        public function getKey() {
            return $this->id;
        }
    };

    // Act
    $service->track('new-checkout', $user, 'viewed');

    // Assert
    $this->assertDatabaseHas('feature_events', [
        'feature_name' => 'new-checkout',
        'user_id' => 789,
        'event_type' => 'viewed',
    ]);
});

it('can track with an object having an id property', function () {
    // Arrange
    $service = app(FlagshipService::class);
    $user = new class {
        public $id = 101;
    };

    // Act
    $service->track('new-checkout', $user, 'clicked');

    // Assert
    $this->assertDatabaseHas('feature_events', [
        'feature_name' => 'new-checkout',
        'user_id' => 101,
        'event_type' => 'clicked',
    ]);
});

it('can track with an array containing an id', function () {
    // Arrange
    $service = app(FlagshipService::class);
    $user = ['id' => 202];

    // Act
    $service->track('new-checkout', $user, 'hovered');

    // Assert
    $this->assertDatabaseHas('feature_events', [
        'feature_name' => 'new-checkout',
        'user_id' => 202,
        'event_type' => 'hovered',
    ]);
});

it('can track with a numeric user id directly', function () {
    // Arrange
    $service = app(FlagshipService::class);
    $userId = 303;

    // Act
    $service->track('new-checkout', $userId, 'dismissed');

    // Assert
    $this->assertDatabaseHas('feature_events', [
        'feature_name' => 'new-checkout',
        'user_id' => 303,
        'event_type' => 'dismissed',
    ]);
});

it('can track with a string user id directly', function () {
    // Arrange
    $service = app(FlagshipService::class);
    $userId = 'user-404';

    // Act
    $service->track('new-checkout', $userId, 'shared');

    // Assert
    $this->assertDatabaseHas('feature_events', [
        'feature_name' => 'new-checkout',
        'user_id' => 'user-404',
        'event_type' => 'shared',
    ]);
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

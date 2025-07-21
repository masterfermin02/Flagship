<?php

namespace Flagship\Tests\Feature;

use Flagship\Models\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use Flagship\FlagshipServiceProvider;
use PHPUnit\Framework\Attributes\Test;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
});

it('can list all features', function () {
    // Create some feature flags
    FeatureFlag::create([
        'name' => 'test_feature_1',
        'is_active' => true,
        'description' => 'Test feature 1',
    ]);

    FeatureFlag::create([
        'name' => 'test_feature_2',
        'is_active' => false,
        'description' => 'Test feature 2',
    ]);

    // Test the endpoint
    $response = $this->getJson('/api/flagship/features');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'test_feature_1')
        ->assertJsonPath('data.1.name', 'test_feature_2');
});

it('can create a feature', function () {
    $response = $this->postJson('/api/flagship/features', [
        'name' => 'new_feature',
        'is_active' => true,
        'description' => 'A new feature',
        'targeting_rules' => ['rule1' => 'value1'],
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'new_feature')
        ->assertJsonPath('data.is_active', true)
        ->assertJsonPath('data.description', 'A new feature');

    $this->assertDatabaseHas('feature_flags', [
        'name' => 'new_feature',
        'is_active' => true,
        'description' => 'A new feature',
    ]);
});

it('can update a feature', function () {
    // Create a feature flag
    FeatureFlag::create([
        'name' => 'update_feature',
        'is_active' => false,
        'description' => 'Feature to update',
    ]);

    // Test the endpoint
    $response = $this->putJson('/api/flagship/features/update_feature', [
        'is_active' => true,
        'description' => 'Updated description',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'update_feature')
        ->assertJsonPath('data.is_active', true)
        ->assertJsonPath('data.description', 'Updated description');

    $this->assertDatabaseHas('feature_flags', [
        'name' => 'update_feature',
        'is_active' => true,
        'description' => 'Updated description',
    ]);
});

it('can delete a feature', function () {
    // Create a feature flag
    FeatureFlag::create([
        'name' => 'delete_feature',
        'is_active' => true,
        'description' => 'Feature to delete',
    ]);

    // Test the endpoint
    $response = $this->deleteJson('/api/flagship/features/delete_feature');

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Feature flag deleted successfully');

    $this->assertDatabaseMissing('feature_flags', [
        'name' => 'delete_feature',
    ]);
});

it('returns 404 when updating nonexistent feature', function () {
    $response = $this->putJson('/api/flagship/features/nonexistent_feature', [
        'is_active' => true,
    ]);

    $response->assertStatus(404)
        ->assertJsonPath('message', 'Feature flag not found');
});

it('validates input when creating feature', function () {
    $response = $this->postJson('/api/flagship/features', [
        // Missing required 'name' field
        'is_active' => true,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('prevents duplicate feature names', function () {
    // Create a feature flag
    FeatureFlag::create([
        'name' => 'existing_feature',
        'is_active' => true,
    ]);

    // Try to create another with the same name
    $response = $this->postJson('/api/flagship/features', [
        'name' => 'existing_feature',
        'is_active' => false,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

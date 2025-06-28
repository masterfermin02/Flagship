<?php

namespace Flagship\Factories;

use Flagship\Models\FeatureFlag;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureFlagFactory extends Factory
{
    protected $model = FeatureFlag::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word . '_feature',
            'description' => $this->faker->sentence,
            'is_active' => $this->faker->boolean,
            'rules' => null,
        ];
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    public function withRules(array $rules)
    {
        return $this->state(function (array $attributes) use ($rules) {
            return [
                'rules' => json_encode($rules),
            ];
        });
    }
}

<?php

namespace Flagship\Http\Controllers;

use Flagship\Contracts\FlagshipInterface;
use Flagship\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class FlagshipApiController extends Controller
{
    /**
     * The Flagship service instance.
     *
     * @var \Flagship\Contracts\FlagshipInterface
     */
    protected $flagship;

    /**
     * Create a new controller instance.
     *
     * @param  \Flagship\Contracts\FlagshipInterface  $flagship
     * @return void
     */
    public function __construct(FlagshipInterface $flagship)
    {
        $this->flagship = $flagship;
    }

    /**
     * Get all feature flags.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->flagship->all(),
        ]);
    }

    /**
     * Create a new feature flag.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:feature_flags,name',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'targeting_rules' => 'nullable|array',
            'targeting_strategy' => 'nullable|string',
            'custom_evaluator' => 'nullable|string',
            'rollout_percentage' => 'nullable|integer|min:0|max:100',
            'variants' => 'nullable|array',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date',
            'environments' => 'nullable|array',
        ]);

        // Create the feature flag
        $this->flagship->create(
            $validated['name'],
            $validated['is_active'] ?? false,
            []
        );

        // Update the feature flag with additional fields
        $featureFlag = FeatureFlag::where('name', $validated['name'])->first();

        // Remove name and is_active from the validated data
        unset($validated['name']);
        unset($validated['is_active']);

        // Update with remaining fields if any
        if (!empty($validated)) {
            $featureFlag->update($validated);
        }

        return response()->json([
            'message' => 'Feature flag created successfully',
            'data' => $featureFlag->fresh(),
        ], 201);
    }

    /**
     * Update an existing feature flag.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $feature
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $feature): JsonResponse
    {
        $featureFlag = FeatureFlag::where('name', $feature)->first();

        if (!$featureFlag) {
            return response()->json([
                'message' => 'Feature flag not found',
            ], 404);
        }

        $validated = $request->validate([
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'targeting_rules' => 'nullable|array',
            'targeting_strategy' => 'nullable|string',
            'custom_evaluator' => 'nullable|string',
            'rollout_percentage' => 'nullable|integer|min:0|max:100',
            'variants' => 'nullable|array',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date',
            'environments' => 'nullable|array',
        ]);

        // Update the feature flag
        $featureFlag->update($validated);

        // If is_active is provided, enable or disable the flag
        if (isset($validated['is_active'])) {
            if ($validated['is_active']) {
                $this->flagship->enable($feature);
            } else {
                $this->flagship->disable($feature);
            }
        }

        return response()->json([
            'message' => 'Feature flag updated successfully',
            'data' => $featureFlag->fresh(),
        ]);
    }

    /**
     * Delete a feature flag.
     *
     * @param  string  $feature
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $feature): JsonResponse
    {
        $featureFlag = FeatureFlag::where('name', $feature)->first();

        if (!$featureFlag) {
            return response()->json([
                'message' => 'Feature flag not found',
            ], 404);
        }

        $this->flagship->delete($feature);

        return response()->json([
            'message' => 'Feature flag deleted successfully',
        ]);
    }
}

<?php

use Flagship\Services\FlagshipService;

it('returns true when no evaluator is configured (fallback)', function () {
    config(['flagship.evaluator' => null]);

    $service = app(FlagshipService::class);

    $result = $this->invokeEvaluateRules($service, [['field' => 'country', 'value' => 'US']], (object)['country' => 'CA']);

    expect($result)->toBeTrue();
});

it('evaluates rules using a closure evaluator', function () {
    config(['flagship.evaluator' => function (array $rules, $user) {
        return collect($rules)->every(fn($rule) => data_get($user, $rule['field']) === $rule['value']);
    }]);

    $service = app(FlagshipService::class);

    $result = $this->invokeEvaluateRules($service, [['field' => 'country', 'value' => 'US']], (object)['country' => 'US']);
    expect($result)->toBeTrue();

    $result = $this->invokeEvaluateRules($service, [['field' => 'country', 'value' => 'US']], (object)['country' => 'CA']);
    expect($result)->toBeFalse();
});

it('uses an invokable class as the evaluator', function () {
    config(['flagship.evaluator' => new class {
        public function __invoke(array $rules, $user): bool
        {
            return collect($rules)->contains(fn($rule) => $rule['field'] === 'country' && $user->country === $rule['value']);
        }
    }]);

    $service = app(FlagshipService::class);

    $result = $this->invokeEvaluateRules($service, [['field' => 'country', 'value' => 'CA']], (object)['country' => 'CA']);
    expect($result)->toBeTrue();

    $result = $this->invokeEvaluateRules($service, [['field' => 'country', 'value' => 'US']], (object)['country' => 'CA']);
    expect($result)->toBeFalse();
});

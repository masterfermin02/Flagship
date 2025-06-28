# Flagship 🚩

A powerful and intelligent feature flag management package for Laravel applications. Take control of your feature rollouts with granular user targeting, A/B testing capabilities, and real-time feature toggling.

## Why Flagship?

Feature flags (also known as feature toggles) allow you to deploy code to production while keeping new features hidden until you're ready to release them. This enables:

- **Safe deployments** - Deploy code without exposing features
- **Gradual rollouts** - Release features to a subset of users first
- **A/B testing** - Test different variations with different user groups
- **Quick rollbacks** - Disable problematic features instantly
- **Production testing** - Test features with real users in production

## Installation

```bash
composer require php-dominicana/flagship
```

Publish the configuration and run migrations:

```bash
php artisan vendor:publish --provider="PhpDominicana\Flagship\FlagshipServiceProvider"
php artisan migrate
```

## Basic Usage

### Checking Feature Flags

```php
use PhpDominicana\Flagship\Facades\Flagship;

// Simple feature check
if (Flagship::isEnabled('new-checkout')) {
    // Show new checkout flow
}

// User-specific feature check
if (Flagship::isEnabledForUser('beta-dashboard', $user)) {
    // Show beta dashboard to specific user
}

// Check with fallback
$showFeature = Flagship::isEnabled('experimental-ui', false);
```

### Blade Directives

```blade
@feature('new-design')
    <div class="new-ui">
        <!-- New design content -->
    </div>
@else
    <div class="legacy-ui">
        <!-- Legacy design content -->
    </div>
@endfeature

@featureForUser('premium-features', $user)
    <div class="premium-content">
        <!-- Premium features -->
    </div>
@endfeature
```

### Middleware Protection

Protect routes based on feature flags:

```php
Route::middleware(['feature:new-api'])->group(function () {
    Route::get('/api/v2/users', [UserController::class, 'index']);
});

// With user context
Route::middleware(['feature:beta-features,user'])->group(function () {
    Route::get('/beta/dashboard', [BetaController::class, 'dashboard']);
});
```

## Feature Configuration

### Creating Features

```php
use PhpDominicana\Flagship\Models\Feature;

// Create a new feature
Feature::create([
    'name' => 'new-checkout',
    'description' => 'New streamlined checkout process',
    'is_active' => true,
    'rollout_percentage' => 25, // 25% of users
]);
```

### Feature Targeting

Target specific user segments:

```php
// Target by user attributes
$feature = Feature::create([
    'name' => 'premium-features',
    'targeting_rules' => [
        'user_type' => 'premium',
        'registration_date' => ['after' => '2024-01-01']
    ]
]);

// Target by custom logic
$feature = Feature::create([
    'name' => 'beta-ui',
    'targeting_strategy' => 'custom',
    'custom_evaluator' => BetaUserEvaluator::class
]);
```

## Advanced Features

### A/B Testing

Create feature variants for A/B testing:

```php
$feature = Feature::create([
    'name' => 'checkout-flow',
    'variants' => [
        'control' => ['weight' => 50],
        'variant_a' => ['weight' => 25],
        'variant_b' => ['weight' => 25]
    ]
]);

// In your code
$variant = Flagship::getVariant('checkout-flow', $user);
switch ($variant) {
    case 'variant_a':
        return view('checkout.variant-a');
    case 'variant_b':
        return view('checkout.variant-b');
    default:
        return view('checkout.control');
}
```

### Scheduled Features

Automatically enable/disable features based on schedule:

```php
Feature::create([
    'name' => 'black-friday-sale',
    'scheduled_start' => '2024-11-29 00:00:00',
    'scheduled_end' => '2024-12-02 23:59:59',
]);
```

### Environment-based Features

Different feature states per environment:

```php
Feature::create([
    'name' => 'debug-toolbar',
    'environments' => [
        'local' => true,
        'staging' => true,
        'production' => false
    ]
]);
```

## Analytics & Monitoring

### Feature Usage Tracking

```php
// Track feature impressions
Flagship::track('new-checkout', $user, 'viewed');

// Track feature interactions
Flagship::track('new-checkout', $user, 'completed_purchase', [
    'amount' => 99.99,
    'items' => 3
]);
```

### Metrics Dashboard

Access built-in analytics:

```php
// Get feature adoption rates
$stats = Flagship::getFeatureStats('new-checkout');
// Returns: ['impressions' => 1000, 'interactions' => 150, 'conversion_rate' => 15%]

// A/B test results
$results = Flagship::getABTestResults('checkout-flow');
```

## Management Interface

### Artisan Commands

```bash
# List all features
php artisan flagship:list

# Enable a feature
php artisan flagship:enable new-checkout

# Disable a feature
php artisan flagship:disable new-checkout

# Set rollout percentage
php artisan flagship:rollout new-checkout --percentage=50

# Cleanup inactive features
php artisan flagship:cleanup
```

### API Endpoints

Built-in REST API for external management:

```http
GET /api/flagship/features
POST /api/flagship/features
PUT /api/flagship/features/{feature}
DELETE /api/flagship/features/{feature}
```

## Configuration

Key configuration options in `config/flagship.php`:

```php
return [
    // Default rollout strategy
    'default_strategy' => 'percentage',
    
    // Cache feature evaluations
    'cache_enabled' => true,
    'cache_ttl' => 300, // 5 minutes
    
    // Analytics tracking
    'analytics_enabled' => true,
    'track_impressions' => true,
    
    // API authentication
    'api_middleware' => ['auth:api'],
    
    // Custom evaluators
    'evaluators' => [
        'beta_users' => App\FeatureEvaluators\BetaUserEvaluator::class,
    ],
];
```

## Custom Evaluators

Create custom logic for feature targeting:

```php
<?php

namespace App\FeatureEvaluators;

use PhpDominicana\Flagship\Contracts\FeatureEvaluator;
use PhpDominicana\Flagship\Models\Feature;

class BetaUserEvaluator implements FeatureEvaluator
{
    public function evaluate(Feature $feature, $user = null): bool
    {
        if (!$user) {
            return false;
        }
        
        return $user->hasRole('beta_tester') && 
               $user->created_at->diffInDays() > 30;
    }
}
```

## Performance Considerations

- **Caching**: Feature evaluations are cached by default
- **Database**: Minimal database queries with eager loading
- **Memory**: Lightweight footprint with lazy loading
- **CDN Ready**: Export feature configs for edge computing

## Security

- **API Protection**: Configurable middleware for API endpoints
- **User Context**: Secure user-based feature evaluation
- **Audit Logs**: Track feature changes and access
- **Rate Limiting**: Built-in protection against abuse

## Requirements

- PHP 8.1+
- Laravel 10.0+
- MySQL 5.7+ / PostgreSQL 10+

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

- 📖 [Documentation](https://docs.flagship.dev)
- 🐛 [Issue Tracker](https://github.com/PHP-Dominicana/Flagship/issues)
- 💬 [Discussions](https://github.com/PHP-Dominicana/Flagship/discussions)
- 🔗 [PHP Dominicana Community](https://php.do)

---

Built with ❤️ by the PHP Dominicana community

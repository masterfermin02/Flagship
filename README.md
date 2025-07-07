# Flagship 🚩

A powerful and intelligent feature flag management package for Laravel applications. Take control of your feature rollouts with granular user targeting, A/B testing capabilities, and real-time feature toggling.

## Why Flagship?

Feature flags (also known as feature toggles) allow you to deploy code to production while keeping new features hidden until you're ready to release them. This enables:

- **Safe deployments** - Deploy code without exposing features
- **Gradual rollouts** - Release features to a subset of users first
- **A/B testing** - Test different variations with different user groups
- **Quick rollbacks** - Disable problematic features instantly
- **Production testing** - Test features with real users in production


## 🚀 Quick Installation

```bash
composer require php-dominicana/flagship
php artisan vendor:publish --provider="Flagship\FlagshipServiceProvider"
php artisan migrate
```

---

## ✨ Creating feature flags

### From Artisan

```bash
php artisan flagship:make new_feature --enabled
php artisan flagship:make beta_feature --description="Beta functionality"
```

### From code

```php
use Flagship\Facades\Flagship;

Flagship::create('new_feature', true);
```

---

## 🔍 Simple checks

```php
if (Flagship::isEnabled('new_feature')) {
    // Code for the new feature
}

if (Flagship::isEnabled('beta_feature', auth()->user())) {
    // User-specific feature
}
```

---

## 🖌 Blade directives

```blade
@flagship('new_feature')
    <div>New feature enabled!</div>
@endflagship
```

---

## 🔗 Middleware

Protect routes or route groups:

```php
Route::middleware(['flagship:new_feature'])->group(function () {
    Route::get('/new-section', [Controller::class, 'newSection']);
});
```

---

## 🛠 Artisan commands

* List all feature flags:

  ```bash
  php artisan flagship:list
  ```

* Toggle a feature on/off:

  ```bash
  php artisan flagship:toggle new_feature
  ```

* Create a new feature:

  ```bash
  php artisan flagship:make my_flag --enabled --description="My description"
  ```

---

## ⚙️ Flexible configuration

You can adjust settings in the published config file:

```php
// config/flagship.php

return [
    'cache_enabled' => true,
    'cache_ttl' => 3600,
    'default_state' => false,
];
```

Or via environment variables:

```
FLAGSHIP_CACHE_ENABLED=true
FLAGSHIP_CACHE_TTL=3600
FLAGSHIP_DEFAULT_STATE=false
```

---

## ⚡ Performance

* Configurable automatic caching to reduce database lookups.
* Optimized SQL queries with proper indexing for lightning-fast checks.

---

## 📚 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

- 📖 [Documentation](https://docs.flagship.dev)
- 🐛 [Issue Tracker](https://github.com/PHP-Dominicana/Flagship/issues)
- 💬 [Discussions](https://github.com/PHP-Dominicana/Flagship/discussions)
- 🔗 [PHP Dominicana Community](https://php.do)

---

Built with ❤️ by the PHP Dominicana community

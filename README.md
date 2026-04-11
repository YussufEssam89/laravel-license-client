# Jo-Tech License Client

A reusable Laravel package that verifies your application's license against the Jo-Tech licensing server (`jo-tech.org`). Provides automatic enforcement via middleware, cached verification, and service-level protection.

## Installation

### Option A: Private Git Repository (recommended for production)

```bash
composer config repositories.jotech-license vcs git@github.com:YussufEssam89/laravel-license-client.git
composer require jotech/license-client
```

### Option B: Path Repository (local development)

```bash
composer config repositories.jotech-license path ../path/to/laravel-license-client
composer require jotech/license-client
```

### Publish Configuration

```bash
php artisan vendor:publish --tag=license-config
```

This creates `config/license.php`.

### Publish Views (optional — for customizing the suspension page)

```bash
php artisan vendor:publish --tag=license-views
```

## Configuration

Add the following to your **`.env`** file:

```env
LICENSE_SECRET=your-license-key-uuid-here
LICENSE_SERVER_URL=https://license.jo-tech.org/api/verify-license
LICENSE_CACHE_HOURS=24
```

| Variable | Description | Default |
|----------|-------------|---------|
| `LICENSE_SECRET` | Your unique license key (UUID) from the admin panel | *required* |
| `LICENSE_SERVER_URL` | URL of the license verification endpoint | `https://license.jo-tech.org/api/verify-license` |
| `LICENSE_CACHE_HOURS` | How many hours to cache the license status | `24` |

## How It Works

1. **Middleware (`CheckLicense`)** is registered globally on package boot
2. On every HTTP request, it checks the cached license status
3. If the cache is expired, it calls the license server to re-verify
4. If the license is not `ACTIVE`, the app shows a professional suspension page
5. Console commands (artisan) are never blocked

### Status Values

| Status | Meaning |
|--------|---------|
| `ACTIVE` | License is valid and active |
| `SUSPENDED` | License was manually suspended by the admin |
| `EXPIRED` | License expiration date has passed |
| `INVALID` | Domain not found or license key mismatch |

## Service-Level Enforcement (Important!)

Middleware alone can be bypassed if removed. For deeper protection, add `enforce()` calls inside critical services:

### In `AppServiceProvider`

```php
use JoTech\License\LicenseManager;

public function boot(): void
{
    if (! app()->runningInConsole()) {
        app(LicenseManager::class)->enforce();
    }
}
```

### In Critical Services

```php
use JoTech\License\LicenseManager;

class PaymentService
{
    public function processPayment($data)
    {
        app(LicenseManager::class)->enforce();
        
        // ... payment logic
    }
}
```

### Recommended Enforcement Points

Add `app(LicenseManager::class)->enforce()` to:

- `AppServiceProvider::boot()`
- Payment/billing services
- Report/export services
- Queue job handlers
- API controllers handling sensitive data

## API Reference

### `LicenseManager`

```php
use JoTech\License\LicenseManager;

$manager = app(LicenseManager::class);

// Get cached status (recommended)
$status = $manager->status(); // 'ACTIVE', 'SUSPENDED', 'EXPIRED', 'INVALID'

// Check if active (boolean)
$isActive = $manager->isActive(); // true/false

// Force fresh verification (bypasses cache)
$status = $manager->verify();

// Clear cache (forces re-verification on next check)
$manager->clearCache();

// Abort if not active (use in services)
$manager->enforce(); // throws 403 if not ACTIVE
```

## Testing in Tinker

```bash
php artisan tinker
```

```php
$manager = app(\JoTech\License\LicenseManager::class);
$manager->verify(); // Forces a fresh check
$manager->status(); // Returns cached status
$manager->clearCache(); // Clears the cache
```

## Offline Tolerance

The package caches the license status for the configured duration (default: 24 hours). This means:

- ✅ The app continues working if the license server is temporarily unreachable
- ✅ No performance impact from frequent API calls
- ✅ If the server is down during cache refresh, the last known status is preserved

## License

Proprietary — Jo-Tech © 2026

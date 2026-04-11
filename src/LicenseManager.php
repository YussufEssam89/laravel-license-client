<?php

namespace JoTech\License;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenseManager
{
    /**
     * Verify license by calling the license server directly.
     * This bypasses the cache and always makes an API call.
     */
    public function verify(): string
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 500)
                ->post(config('license.server_url'), [
                    'domain' => request()->getHost(),
                    'secret' => config('license.secret'),
                ]);

            if ($response->successful()) {
                return $response->json('status', 'INVALID');
            }

            // If server returned 403, the secret is wrong
            if ($response->status() === 403) {
                Log::warning('JoTech License: Invalid license key (403).');
                return 'INVALID';
            }

            Log::warning('JoTech License: Server returned HTTP ' . $response->status());
            return $this->fallbackStatus();

        } catch (\Exception $e) {
            Log::warning('JoTech License: Could not reach license server.', [
                'error' => $e->getMessage(),
            ]);

            // Return cached status if available, otherwise fail open temporarily
            return $this->fallbackStatus();
        }
    }

    /**
     * Get cached license status. Calls verify() only when cache expires.
     * This is the primary method to use — provides performance + offline tolerance.
     */
    public function status(): string
    {
        $cacheHours = config('license.cache_hours', 24);
        if ($cacheHours <= 0) {
            return $this->verify();
        }

        return Cache::remember(
            'jotech_license_status',
            now()->addHours($cacheHours),
            fn () => $this->verify()
        );
    }

    /**
     * Check if the license is currently active.
     */
    public function isActive(): bool
    {
        return $this->status() === 'ACTIVE';
    }

    /**
     * Clear the cached license status, forcing a fresh verification on next check.
     */
    public function clearCache(): void
    {
        Cache::forget('jotech_license_status');
    }

    /**
     * Enforce license — block the app if license is not active or not configured.
     * Use this inside service classes, providers, and critical controllers.
     *
     * LICENSE_SECRET is required — missing secret is treated as INVALID.
     *
     * Example:
     *   app(LicenseManager::class)->enforce();
     */
    public function enforce(): void
    {
        // No secret configured — treat as invalid (secret is required)
        if (empty(config('license.secret'))) {
            $this->block('INVALID');
            return;
        }

        if (! $this->isActive()) {
            $this->block($this->status());
        }
    }

    /**
     * Block the application with a 403 response.
     * Returns JSON for API requests, or the styled suspended view for web requests.
     */
    protected function block(string $status): void
    {
        if (request()->expectsJson()) {
            response()->json([
                'message' => 'LICENSE IS NOT ACTIVE.',
                'status'  => $status,
            ], 403)->send();
        } else {
            response()->view('license::license.suspended', [
                'status' => $status,
            ], 403)->send();
        }

        exit;
    }

    /**
     * Fallback: return last known cached status, or 'INVALID' if none exists.
     */
    protected function fallbackStatus(): string
    {
        return Cache::get('jotech_license_status', 'INVALID');
    }
}

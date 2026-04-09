<?php

namespace JoTech\License\Middleware;

use Closure;
use Illuminate\Http\Request;
use JoTech\License\LicenseManager;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    protected LicenseManager $licenseManager;

    public function __construct(LicenseManager $licenseManager)
    {
        $this->licenseManager = $licenseManager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip license check in console (artisan commands)
        if (app()->runningInConsole()) {
            return $next($request);
        }

        // Skip license check if no secret is configured (development/testing)
        if (empty(config('license.secret'))) {
            return $next($request);
        }

        $status = $this->licenseManager->status();

        if ($status !== 'ACTIVE') {
            return response()->view('license::license.suspended', [
                'status' => $status,
            ], 503);
        }

        return $next($request);
    }
}

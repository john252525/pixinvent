<?php

namespace App\Http\Middleware;

use App\Http\Integrations\ExternalTokenGate\ExternalTokenConnector;
use App\Http\Integrations\ExternalTokenGate\Requests\ExternalTokenGate;
use Closure;
use Illuminate\Http\Request;

class LocalizationMiddleware
{
    protected const ALLOWED_LOCALIZATIONS = ['en', 'ru'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $localization = $request->header('Accept-Language');
        $localization = in_array($localization, self::ALLOWED_LOCALIZATIONS, true) ? $localization : 'en';
        app()->setLocale($localization);

        return $next($request);
    }
}

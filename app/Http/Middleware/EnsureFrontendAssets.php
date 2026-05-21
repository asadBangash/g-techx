<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFrontendAssets
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('install*') && ! file_exists(public_path('build/manifest.json'))) {
            return response()->view('errors.missing-build', [], 503);
        }

        return $next($request);
    }
}

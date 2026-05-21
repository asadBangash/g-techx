<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInstallation
{
    public function handle(Request $request, Closure $next)
    {
        if (! isAppInstalled() && ! $request->is('install*')) {
            return redirect()->route('installer.welcome');
        }

        if (isAppInstalled() && $request->is('install*')) {
            return redirect()->route('landing.page');
        }

        return $next($request);
    }
}

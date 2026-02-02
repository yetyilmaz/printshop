<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Only track GET requests and ignore internal/admin routes if needed
            // But user said "number of visitors per day", so main site traffic.
            if ($request->isMethod('get') && !$request->is('admin/*', 'build/*', 'storage/*')) {
                \App\Models\Visit::firstOrCreate([
                    'ip_address' => $request->ip(),
                    'visit_date' => now()->toDateString(),
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail to not disrupt user experience
        }

        return $next($request);
    }
}

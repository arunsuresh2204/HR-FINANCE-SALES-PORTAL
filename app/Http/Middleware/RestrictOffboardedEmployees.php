<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Once an employee's offboarding is fully complete, they keep the ability to
 * log in — but only to retrieve their final documents. Everything else in
 * the portal redirects them to the Exit Documents page.
 */
class RestrictOffboardedEmployees
{
    /**
     * Route names an offboarded employee may still reach.
     */
    protected const ALLOWED_ROUTES = [
        'hr.exit-documents',
        'resignations.experience-certificate',
        'resignations.relieving-letter',
        'payslips.download',
        'logout',
        'livewire.update',
        'default.livewire.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || $user->employment_status !== 'offboarded') {
            return $next($request);
        }

        if (in_array($request->route()?->getName(), self::ALLOWED_ROUTES, true)) {
            return $next($request);
        }

        return redirect()->route('hr.exit-documents');
    }
}

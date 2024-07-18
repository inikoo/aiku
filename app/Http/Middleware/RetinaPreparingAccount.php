<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RetinaPreparingAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     * @return \Inertia\Response
     */
    public function handle(Request $request, Closure $next)
    {
        if(blank($request->user()->customer->fulfilmentCustomer->rentalAgreement)) {
            abort(403, 'We still preparing your account');
        }

        return $next($request);
    }
}

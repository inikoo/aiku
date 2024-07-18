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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->user() && is_null($request->user()?->customer?->fulfilmentCustomer?->rentalAgreement)) {
            return redirect()->route('retina.prepare-account.show');
        }

        return $next($request);
    }
}

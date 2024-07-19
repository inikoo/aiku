<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        if($request->user() && is_null($request->user()?->customer?->fulfilmentCustomer?->rentalAgreement)) {
            return Inertia::render('Errors/ErrorInApp', [
                'error' => [
                    'code'        => 403,
                    'title'       => 'We still prepare your account',
                    'description' => 'please come back shortly.'
                ]
            ]);
        }

        return $next($request);
    }
}
<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Mar 2023 22:07:24 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\LogUserFirebaseMiddleware;
use App\Http\Middleware\LogUserRequestMiddleware;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\HandleCentralInertiaRequests;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Monicahq\Cloudflare\Http\Middleware\TrustProxies;
use Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [


        'central-api' => [
            ForceJsonResponse::class,
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            SubstituteBindings::class,
            'auth:api-admin-user'
        ],

        'central-web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            HandleCentralInertiaRequests::class,
            SetLocale::class,
        ],
        'app'     => [
            NeedsTenant::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            HandleInertiaRequests::class,
            EnsureValidTenantSession::class,
            SetLocale::class,
            LogUserRequestMiddleware::class,
            LogUserFirebaseMiddleware::class
        ],
        // for use in cypress
        'web'     => [
            NeedsTenant::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            HandleInertiaRequests::class,
            EnsureValidTenantSession::class,
            SetLocale::class,
            LogUserRequestMiddleware::class,
            LogUserFirebaseMiddleware::class
        ],

        'api'            => [
            'throttle:api',
            SubstituteBindings::class,
            'auth:admin-user'
        ],
        /*
        'api-admin-user' => [
            'throttle:api',
            SubstituteBindings::class,
            LogRequest::class,
            'auth:api-admin-user'
        ],
*/


        /*
        'api-tenant' => [
            'throttle:api',
            SubstituteBindings::class,
            LogRequest::class,
            'auth:tenant'
        ],
        */
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth'             => Authenticate::class,
        'auth.basic'       => AuthenticateWithBasicAuth::class,
        'auth.session'     => AuthenticateSession::class,
        'cache.headers'    => SetCacheHeaders::class,
        'can'              => Authorize::class,
        'guest'            => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePassword::class,
        'signed'           => ValidateSignature::class,
        'throttle'         => ThrottleRequests::class,
        'verified'         => EnsureEmailIsVerified::class,
        'inertia'          => HandleInertiaRequests::class,

    ];
}

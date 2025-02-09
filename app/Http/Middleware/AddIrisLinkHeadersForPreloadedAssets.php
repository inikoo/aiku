<?php

namespace App\Http\Middleware;

use Illuminate\Support\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Vite;

class AddIrisLinkHeadersForPreloadedAssets
{

    public function handle($request, $next)
    {
        return tap($next($request), function ($response) {
            if ($response instanceof Response && Vite::preloadedAssets() !== []) {

               // dd(Vite::preloadedAssets());
                $response->header('Link', (new Collection(Vite::preloadedAssets()))
                    ->reject(function ($attributes, $url) {
                       // dd($url);
                    })
                    ->map(fn ($attributes, $url) => "<{$url}>; ".implode('; ', $attributes))
                    ->join(', '), false);
            }
        });
    }
}

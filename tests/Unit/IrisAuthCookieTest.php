<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use App\Actions\Traits\WithIrisAuthCookie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

$storefrontSession = fn () => new class () {
    use WithIrisAuthCookie;

    public function markLoggedIn(): void
    {
        $this->queueIrisAuthCookie();
    }

    public function markLoggedOut(): void
    {
        $this->forgetIrisAuthCookie();
    }
};

$queuedAuthCookie = fn () => collect(Cookie::getQueuedCookies())
    ->first(fn ($cookie) => $cookie->getName() === 'iris_vua');

test('the storefront session flag is readable by javascript', function () use ($storefrontSession, $queuedAuthCookie) {
    $storefrontSession()->markLoggedIn();

    $cookie = $queuedAuthCookie();

    expect($cookie)->not->toBeNull()
        ->and($cookie->isHttpOnly())->toBeFalse()
        ->and($cookie->getValue())->not->toBeEmpty()
        ->and($cookie->getExpiresTime())->toBeGreaterThan(now()->timestamp);
});

test('logging out expires the storefront session flag', function () use ($storefrontSession, $queuedAuthCookie) {
    $storefrontSession()->markLoggedIn();
    $storefrontSession()->markLoggedOut();

    $cookie = $queuedAuthCookie();

    expect($cookie)->not->toBeNull()
        ->and($cookie->getValue())->toBeEmpty()
        ->and($cookie->getExpiresTime())->toBeLessThan(now()->timestamp);
});

test('the storefront session flag lives as long as the session when the web user was not remembered', function () use ($storefrontSession, $queuedAuthCookie) {
    config(['session.lifetime' => 4320]);

    $storefrontSession()->markLoggedIn();

    expect($queuedAuthCookie()->getExpiresTime())->toBeBetween(now()->addMinutes(4319)->timestamp, now()->addMinutes(4321)->timestamp);
});

test('the storefront session flag lives as long as the remember me cookie when the web user is remembered', function () use ($storefrontSession, $queuedAuthCookie) {
    config(['session.lifetime' => 4320]);
    $recallerName = Auth::guard('retina')->getRecallerName();

    request()->cookies->set($recallerName, 'remembered');
    $storefrontSession()->markLoggedIn();
    request()->cookies->remove($recallerName);

    expect($queuedAuthCookie()->getExpiresTime())->toBeGreaterThan(now()->addDays(399)->timestamp);
});

test('the storefront session flag lives as long as the remember me cookie on the login that sets it', function () use ($storefrontSession, $queuedAuthCookie) {
    config(['session.lifetime' => 4320]);

    Cookie::queue(Auth::guard('retina')->getRecallerName(), 'remembered', 576000);
    $storefrontSession()->markLoggedIn();

    expect($queuedAuthCookie()->getExpiresTime())->toBeGreaterThan(now()->addDays(399)->timestamp);
});

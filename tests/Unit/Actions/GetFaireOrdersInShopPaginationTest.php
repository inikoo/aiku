<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 23 Jul 2026 11:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\External\Faire\GetFaireOrdersInShop;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Facades\Http;

test('follows faire cursor pagination until exhausted', function () {
    Http::fake(function ($request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY) ?: '', $query);
        if ($query['cursor'] ?? null) {
            return Http::response(['orders' => [], 'cursor' => null]);
        }

        return Http::response(['orders' => [], 'cursor' => 'next-page-cursor']);
    });

    $shop = new Shop();
    $shop->settings = ['faire' => ['access_token' => 'test-token']];

    GetFaireOrdersInShop::make()->handle($shop);

    Http::assertSentCount(4);
    Http::assertSent(function ($request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY) ?: '', $query);

        return ($query['cursor'] ?? null) === 'next-page-cursor'
            && !isset($query['excluded_states']);
    });
});

test('also pulls recently cancelled faire orders so cancellations on faire reach aiku', function () {
    Http::fake(fn () => Http::response(['orders' => [], 'cursor' => null]));

    $shop           = new Shop();
    $shop->settings = ['faire' => ['access_token' => 'test-token']];

    GetFaireOrdersInShop::make()->handle($shop);

    Http::assertSent(function ($request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY) ?: '', $query);
        $excludedStates = explode(',', $query['excluded_states'] ?? '');

        return isset($query['updated_at_min'])
            && !in_array('CANCELED', $excludedStates)
            && in_array('NEW', $excludedStates)
            && in_array('PROCESSING', $excludedStates);
    });
});

test('also pulls faire orders on their way so backorders that ship directly reach aiku, but never delivered ones', function () {
    Http::fake(fn () => Http::response(['orders' => [], 'cursor' => null]));

    $shop           = new Shop();
    $shop->settings = ['faire' => ['access_token' => 'test-token']];

    GetFaireOrdersInShop::make()->handle($shop);

    Http::assertSent(function ($request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY) ?: '', $query);
        $excludedStates = explode(',', $query['excluded_states'] ?? '');

        return isset($query['updated_at_min'])
            && !in_array('PRE_TRANSIT', $excludedStates)
            && !in_array('IN_TRANSIT', $excludedStates)
            && in_array('DELIVERED', $excludedStates);
    });
});

test('records and clears skipped faire orders in shop settings', function () {
    $action = GetFaireOrdersInShop::make();

    $shop           = new Shop();
    $shop->settings = ['faire' => ['access_token' => 'test-token']];

    $action->recordSkippedFaireOrder($shop, ['id' => 'bo_1', 'display_id' => 'ABC123'], ['Product not found in catalogue']);

    expect($shop->settings['faire']['skipped_orders']['bo_1']['display_id'])->toBe('ABC123')
        ->and($shop->settings['faire']['skipped_orders']['bo_1']['reasons'])->toBe(['Product not found in catalogue'])
        ->and($shop->settings['faire']['skipped_orders']['bo_1']['first_seen'])->not->toBeNull();

    $firstSeen = $shop->settings['faire']['skipped_orders']['bo_1']['first_seen'];
    $action->recordSkippedFaireOrder($shop, ['id' => 'bo_1', 'display_id' => 'ABC123'], ['Retailer not found on Faire']);

    expect($shop->settings['faire']['skipped_orders']['bo_1']['first_seen'])->toBe($firstSeen)
        ->and($shop->settings['faire']['skipped_orders']['bo_1']['reasons'])->toBe(['Retailer not found on Faire']);

    $action->clearSkippedFaireOrder($shop, 'unknown-id');
    expect($shop->settings['faire']['skipped_orders'])->toHaveKey('bo_1');

    $action->clearSkippedFaireOrder($shop, 'bo_1');
    expect($shop->settings['faire']['skipped_orders'])->toBe([]);
});

test('pulls backordered faire orders so they are shipped from aiku, not from the faire portal', function () {
    Http::fake(fn () => Http::response(['orders' => [], 'cursor' => null]));

    $shop           = new Shop();
    $shop->settings = ['faire' => ['access_token' => 'test-token']];

    GetFaireOrdersInShop::make()->handle($shop);

    Http::assertSent(function ($request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY) ?: '', $query);
        $excludedStates = explode(',', $query['excluded_states'] ?? '');

        return isset($query['created_at_min'])
            && !in_array('BACKORDERED', $excludedStates)
            && in_array('CANCELED', $excludedStates);
    });
});

test('a backorder is accepted on faire no earlier than its ship after date', function () {
    Carbon\Carbon::setTestNow('2026-10-22 08:00:00');

    $shop           = new Shop();
    $shop->settings = ['faire' => ['order_from_days' => 6]];
    $action         = App\Actions\Catalogue\Shop\External\Faire\AcceptFaireOrder::make();

    expect($action->getExpectedShipDate($shop, null)->toDateString())->toBe('2026-10-28')
        ->and($action->getExpectedShipDate($shop, '2026-10-30T00:00:00.000Z')->toDateString())->toBe('2026-10-30')
        ->and($action->getExpectedShipDate($shop, '2026-10-20T00:00:00.000Z')->toDateString())->toBe('2026-10-28');

    Carbon\Carbon::setTestNow();
});

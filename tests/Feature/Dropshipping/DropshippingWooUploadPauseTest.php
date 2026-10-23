<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\Dropshipping\WooCommerce\CheckWooChannel;
use App\Actions\Dropshipping\WooCommerce\PingActiveWooChannel;
use App\Actions\Dropshipping\WooCommerce\ProbeWooStoreFromServer;
use App\Actions\Dropshipping\WooCommerce\Product\DispatchWooUploadQueue;
use App\Actions\Dropshipping\WooCommerce\Product\StoreNewProductToCurrentWooCommerce;
use App\Actions\Dropshipping\WooCommerce\TestConnectionWooCommerceUser;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Dropshipping\CustomerSalesChannelStateEnum;
use App\Enums\Dropshipping\WooCommerceConnectionFailureEnum;
use App\Models\Catalogue\Shop;
use App\Models\Dropshipping\CustomerSalesChannel;
use App\Models\Dropshipping\WooCommerceUser;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Laravel\Horizon\Contracts\SupervisorRepository;
use Lorisleiva\Actions\Decorators\JobDecorator;

use function Pest\Laravel\actingAs;

require_once __DIR__.'/../../Support/WooCommerceHelpers.php';

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = $this->organisation->group;
    $this->user         = createAdminGuest($this->group)->getUser();

    $shop = Shop::first();
    if (!$shop) {
        $storeData = Shop::factory()->definition();
        data_set($storeData, 'type', ShopTypeEnum::DROPSHIPPING);

        $shop = StoreShop::make()->action($this->organisation, $storeData);
    }
    $this->shop = UpdateShop::make()->action($shop, ['state' => ShopStateEnum::OPEN]);

    $this->customer = createCustomer($this->shop);

    list($this->tradeUnit, $this->product) = createProduct($this->shop);

    actingAs($this->user);
});

function wooStoreNotAnswering(): void
{
    Http::swap(new Factory(app(Dispatcher::class)));
    Http::fake(['*' => Http::failedConnection('cURL error 28: Operation timed out after 30001 milliseconds with 0 bytes received')]);
    Http::preventStrayRequests();
}

function wooPausedChannel(WooCommerceUser $wooCommerceUser): CustomerSalesChannel
{
    $wooCommerceUser->customerSalesChannel->update(['can_connect_to_platform' => false, 'platform_status' => false]);

    return $wooCommerceUser->customerSalesChannel->refresh();
}

function wooProbeServers(array $queues): void
{
    $supervisors = array_map(fn (string $queue) => (object) [
        'name'      => Str::after($queue, ProbeWooStoreFromServer::SUPERVISOR.'-').'-AbCd:'.ProbeWooStoreFromServer::SUPERVISOR,
        'processes' => [$queue => 1],
        'options'   => ['queue' => $queue],
    ], $queues);

    app()->instance(SupervisorRepository::class, Mockery::mock(SupervisorRepository::class, ['all' => $supervisors]));
}

test('uploads of a bulk to a store that stops answering pause the store after a few misses, and the waiting uploads are not sent', function () {
    Queue::fake();

    $wooCommerceUser = wooConnect(wooCustomer($this->shop));
    $channel         = $wooCommerceUser->customerSalesChannel;
    $channel->update(['can_connect_to_platform' => true, 'platform_status' => true]);
    wooClearUploadQueue($wooCommerceUser);
    Redis::del('woo_connection_failures_'.$wooCommerceUser->id);

    $portfolios = wooUploadPortfolios($this, $channel, StoreNewProductToCurrentWooCommerce::CONNECTION_FAILURES_BEFORE_PAUSE + 1, 'aw-paused');
    $waiting    = $portfolios->pop();

    wooStoreNotAnswering();
    StoreNewProductToCurrentWooCommerce::run($wooCommerceUser, $portfolios[0], false);
    $afterOneMiss = $channel->refresh()->can_connect_to_platform;

    $portfolios->slice(1)->each(fn ($portfolio) => StoreNewProductToCurrentWooCommerce::run($wooCommerceUser, $portfolio, false));

    DispatchWooUploadQueue::push($wooCommerceUser, $waiting);
    DispatchWooUploadQueue::run($wooCommerceUser);

    $channel->refresh();

    expect($afterOneMiss)->toBeTrue()
        ->and($channel->can_connect_to_platform)->toBeFalse()
        ->and($channel->platform_status)->toBeFalse()
        ->and($channel->state)->toBe(CustomerSalesChannelStateEnum::NOT_READY)
        ->and($channel->connection_failure)->toBe(WooCommerceConnectionFailureEnum::TIMEOUT)
        ->and($portfolios->every(fn ($portfolio) => $portfolio->refresh()->errors_response['connection_failure'] === WooCommerceConnectionFailureEnum::TIMEOUT->value))->toBeTrue()
        ->and(wooQueuedCreates())->toHaveCount(0)
        ->and(Redis::llen(DispatchWooUploadQueue::queueKey($wooCommerceUser)))->toBe(1);
});

test('a store that misses an answer and then takes an upload is not paused', function () {
    $wooCommerceUser = wooConnect(wooCustomer($this->shop));
    $channel         = $wooCommerceUser->customerSalesChannel;
    $channel->update(['can_connect_to_platform' => true, 'platform_status' => true]);
    Redis::del('woo_connection_failures_'.$wooCommerceUser->id);

    [$first, $second, $third, $fourth] = wooUploadPortfolios($this, $channel, 4, 'aw-blip')->all();

    wooStoreNotAnswering();
    StoreNewProductToCurrentWooCommerce::run($wooCommerceUser, $first);
    StoreNewProductToCurrentWooCommerce::run($wooCommerceUser, $second);

    wooFake(['POST products' => Http::response(wooProduct(811, ['sku' => 'aw-blip-3']), 201)]);
    StoreNewProductToCurrentWooCommerce::run($wooCommerceUser, $third);

    wooStoreNotAnswering();
    StoreNewProductToCurrentWooCommerce::run($wooCommerceUser, $fourth);

    expect($third->refresh()->platform_product_id)->toBe('811')
        ->and($first->refresh()->errors_response['message'])->not->toContain('timed out')
        ->and($channel->refresh()->can_connect_to_platform)->toBeTrue();
});

test('a create already queued when its store paused goes back to the list without asking the store', function () {
    $wooCommerceUser = wooConnect(wooCustomer($this->shop));
    wooPausedChannel($wooCommerceUser);
    wooClearUploadQueue($wooCommerceUser);

    $portfolio = wooPortfolio($wooCommerceUser->customerSalesChannel, $this->product, null, 'aw-paused-in-flight');
    DispatchWooUploadQueue::reserveSlot($wooCommerceUser, $portfolio);

    wooStoreNotAnswering();
    StoreNewProductToCurrentWooCommerce::make()->asJob(Mockery::mock(JobDecorator::class), $wooCommerceUser, $portfolio);

    expect(Http::recorded())->toHaveCount(0)
        ->and(Redis::zscore(DispatchWooUploadQueue::reservedKey($wooCommerceUser), $portfolio->id))->toBeFalse()
        ->and(Redis::lrange(DispatchWooUploadQueue::queueKey($wooCommerceUser), 0, -1))->toHaveCount(1)
        ->and(json_decode(Redis::lindex(DispatchWooUploadQueue::queueKey($wooCommerceUser), 0), true)['portfolio_id'])->toBe($portfolio->id);
});

test('when the store answers again the check resumes its uploads once each, the ones that could not connect included', function () {
    Queue::fake();

    $wooCommerceUser = wooConnect(wooCustomer($this->shop));
    $channel         = $wooCommerceUser->customerSalesChannel;
    $channel->update(['can_connect_to_platform' => true, 'platform_status' => true]);
    wooClearUploadQueue($wooCommerceUser);

    [$failed, $waiting, $otherError, $failedAndWaiting] = wooUploadPortfolios($this, $channel, 4, 'aw-resume')->all();
    $otherError->update(['errors_response' => ['message' => 'Invalid or duplicated SKU.']]);

    wooStoreNotAnswering();
    StoreNewProductToCurrentWooCommerce::run($wooCommerceUser, $failed);
    StoreNewProductToCurrentWooCommerce::run($wooCommerceUser, $failedAndWaiting);
    wooPausedChannel($wooCommerceUser);
    DispatchWooUploadQueue::push($wooCommerceUser, $waiting);
    DispatchWooUploadQueue::push($wooCommerceUser, $failedAndWaiting);

    wooFakeForPing();
    CheckWooChannel::run($wooCommerceUser->refresh());

    $created = wooQueuedCreates()->map(fn ($job) => $job->getParameters()[1]->id)->sort()->values()->all();

    expect($channel->refresh()->can_connect_to_platform)->toBeTrue()
        ->and($created)->toBe(collect([$failed->id, $waiting->id, $failedAndWaiting->id])->sort()->values()->all());
});

test('trying again with no probe worker running asks the store from here and resumes the store when it answers', function () {
    $wooCommerceUser = wooConnect(wooCustomer($this->shop));
    wooPausedChannel($wooCommerceUser);
    wooProbeServers([]);

    wooFakeForPing();
    $answer = TestConnectionWooCommerceUser::make()->handle($wooCommerceUser->customerSalesChannel);

    $results = ProbeWooStoreFromServer::results($wooCommerceUser);

    expect($answer)->toBeNull()
        ->and($results['checking'])->toBeFalse()
        ->and($results['servers'])->toHaveCount(1)
        ->and($results['servers'][0]['ok'])->toBeTrue()
        ->and($wooCommerceUser->customerSalesChannel->refresh()->can_connect_to_platform)->toBeTrue();
});

test('trying again on a parked channel resumes its uploads but leaves orders and stock off', function () {
    $wooCommerceUser = wooConnect(wooCustomer($this->shop));
    $channel         = wooPausedChannel($wooCommerceUser);
    $channel->update(['ping_error_count' => PingActiveWooChannel::PARKED_AFTER_FAILURES, 'state' => CustomerSalesChannelStateEnum::NOT_READY]);
    wooProbeServers([]);

    wooFakeForPing();
    TestConnectionWooCommerceUser::make()->handle($channel);

    $channel->refresh();

    expect($channel->can_connect_to_platform)->toBeTrue()
        ->and($channel->platform_status)->toBeFalse()
        ->and($channel->state)->toBe(CustomerSalesChannelStateEnum::NOT_READY)
        ->and($channel->ping_error_count)->toBe(PingActiveWooChannel::PARKED_AFTER_FAILURES);
});

test('trying again asks the store from every server and resumes only once all of them get an answer', function () {
    Queue::fake();

    $wooCommerceUser = wooConnect(wooCustomer($this->shop));
    wooPausedChannel($wooCommerceUser);
    wooProbeServers(['woo-probe-boro', 'woo-probe-litio']);

    $answer = TestConnectionWooCommerceUser::make()->handle($wooCommerceUser->customerSalesChannel);
    $again  = TestConnectionWooCommerceUser::make()->handle($wooCommerceUser->customerSalesChannel);

    Queue::assertPushedOn('woo-probe-boro', JobDecorator::class, fn (JobDecorator $job) => $job->getAction() instanceof ProbeWooStoreFromServer);
    Queue::assertPushedOn('woo-probe-litio', JobDecorator::class, fn (JobDecorator $job) => $job->getAction() instanceof ProbeWooStoreFromServer);
    $probesQueued = Queue::pushed(JobDecorator::class, fn (JobDecorator $job) => $job->getAction() instanceof ProbeWooStoreFromServer)->count();

    wooFakeForPing();
    ProbeWooStoreFromServer::run($wooCommerceUser, 'woo-probe-boro');
    $halfway = ProbeWooStoreFromServer::results($wooCommerceUser);

    wooStoreNotAnswering();
    ProbeWooStoreFromServer::run($wooCommerceUser, 'woo-probe-litio');
    $oneFailed = ProbeWooStoreFromServer::results($wooCommerceUser);
    $stillPaused = !$wooCommerceUser->customerSalesChannel->refresh()->can_connect_to_platform;

    wooFakeForPing();
    ProbeWooStoreFromServer::run($wooCommerceUser, 'woo-probe-litio');
    $allAnswered = ProbeWooStoreFromServer::results($wooCommerceUser);

    expect($answer)->toBe(['checking' => true])
        ->and($again)->toBe(['checking' => true])
        ->and($probesQueued)->toBe(2)
        ->and($halfway['checking'])->toBeTrue()
        ->and($oneFailed['checking'])->toBeFalse()
        ->and(collect($oneFailed['servers'])->pluck('ok')->all())->toBe([true, false])
        ->and($oneFailed['servers'][1]['reason'])->toBe(WooCommerceConnectionFailureEnum::TIMEOUT->customerMessage())
        ->and($stillPaused)->toBeTrue()
        ->and($allAnswered['checking'])->toBeFalse()
        ->and($wooCommerceUser->customerSalesChannel->refresh()->can_connect_to_platform)->toBeTrue();
});

test('each server listens to its own probe queue', function () {
    expect(config('horizon.defaults.woo-probe.queue'))->toBe([ProbeWooStoreFromServer::serverQueue()])
        ->and(config('horizon.environments.production.woo-probe.maxProcesses'))->toBe(1);
});

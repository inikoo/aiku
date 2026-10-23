<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\WooCommerce;

use App\Actions\Dropshipping\WooCommerce\Product\DispatchWooUploadQueue;
use App\Models\Dropshipping\WooCommerceUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Horizon\Contracts\SupervisorRepository;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Each of our servers reaches a store from its own IP address, and a store's firewall can let one
 * through and block another. The customer's try again asks the store from every server running
 * Horizon, each through the woo-probe queue only that server listens to, and the uploads resume
 * only when every one of them gets an answer. A parked channel gets only its uploads back, see
 * PingActiveWooChannel.
 */
class ProbeWooStoreFromServer
{
    use AsAction;

    public const string SUPERVISOR = 'woo-probe';

    public const int PROBE_TIMEOUT_SECONDS = 15;

    private const int RESULTS_KEPT_SECONDS = 3600;

    /**
     * A check still running is not started again by another click: the probe worker on each server
     * is a single process shared by every store.
     */
    private const int CHECK_AT_MOST_EVERY_SECONDS = 60;

    public static function serverQueue(): string
    {
        return self::SUPERVISOR.'-'.Str::slug(gethostname());
    }

    /**
     * Without a woo-probe worker anywhere (staging, local, tests) the store is asked from here.
     *
     * @return array<int, string>
     */
    public static function start(WooCommerceUser $wooCommerceUser): array
    {
        $running = Cache::get(self::resultsKey($wooCommerceUser));
        if ($running && self::results($wooCommerceUser)['checking'] && now()->subSeconds(self::CHECK_AT_MOST_EVERY_SECONDS)->lt($running['requested_at'])) {
            return $running['queues'];
        }

        $serverQueues = self::serverQueues();
        $queues       = $serverQueues ?: [self::serverQueue()];

        Cache::put(self::resultsKey($wooCommerceUser), ['requested_at' => now()->toIso8601String(), 'queues' => $queues], self::RESULTS_KEPT_SECONDS);
        Cache::forget(self::resultsKey($wooCommerceUser).'_claimed');
        Cache::forget(self::resultsKey($wooCommerceUser).'_finished');
        foreach ($queues as $queue) {
            Cache::forget(self::resultsKey($wooCommerceUser).'_'.$queue);
        }

        if ($serverQueues === []) {
            self::run($wooCommerceUser, self::serverQueue());

            return $queues;
        }

        foreach ($queues as $queue) {
            self::dispatch($wooCommerceUser, $queue)->onQueue($queue);
        }

        return $queues;
    }

    public function handle(WooCommerceUser $wooCommerceUser, string $queue): void
    {
        $probe = ['success' => false, 'failure' => null];

        try {
            $wooCommerceUser->setTimeout(self::PROBE_TIMEOUT_SECONDS);
            $probe = $wooCommerceUser->probeConnection();
        } finally {
            Cache::put(self::resultsKey($wooCommerceUser).'_'.$queue, [
                'ip'     => self::serverIp(),
                'ok'     => $probe['success'],
                'reason' => $probe['failure']?->customerMessage(),
            ], self::RESULTS_KEPT_SECONDS);
        }

        if (!self::everyServerAnswered($wooCommerceUser)) {
            return;
        }

        if (!Cache::add(self::resultsKey($wooCommerceUser).'_claimed', true, self::RESULTS_KEPT_SECONDS)) {
            return;
        }

        $wooCommerceUser = $wooCommerceUser->fresh();

        try {
            if ($wooCommerceUser->customerSalesChannel->ping_error_count >= PingActiveWooChannel::PARKED_AFTER_FAILURES) {
                DispatchWooUploadQueue::unpause($wooCommerceUser);
            } else {
                CheckWooChannel::run($wooCommerceUser);
            }
        } finally {
            Cache::put(self::resultsKey($wooCommerceUser).'_finished', true, self::RESULTS_KEPT_SECONDS);
        }
    }

    /**
     * @return array{requested_at: string, checking: bool, servers: array<int, array{ip: ?string, ok: bool, reason: ?string}>}|null
     */
    public static function results(WooCommerceUser $wooCommerceUser): ?array
    {
        $request = Cache::get(self::resultsKey($wooCommerceUser));
        if (!$request) {
            return null;
        }

        $servers = self::serverResults($request['queues'], $wooCommerceUser);

        return [
            'requested_at' => $request['requested_at'],
            'checking'     => $servers->contains(null) || (self::everyServerAnswered($wooCommerceUser) && !Cache::get(self::resultsKey($wooCommerceUser).'_finished')),
            'servers'      => $servers->filter()->values()->all(),
        ];
    }

    private static function everyServerAnswered(WooCommerceUser $wooCommerceUser): bool
    {
        $request = Cache::get(self::resultsKey($wooCommerceUser));
        $servers = $request ? self::serverResults($request['queues'], $wooCommerceUser) : collect();

        return $servers->isNotEmpty() && $servers->every(fn (?array $server) => $server && $server['ok']);
    }

    /**
     * @param array<int, string> $queues
     */
    private static function serverResults(array $queues, WooCommerceUser $wooCommerceUser): Collection
    {
        return collect($queues)->map(fn (string $queue) => Cache::get(self::resultsKey($wooCommerceUser).'_'.$queue));
    }

    /**
     * @return array<int, string>
     */
    private static function serverQueues(): array
    {
        return collect(app(SupervisorRepository::class)->all())
            ->filter(fn ($supervisor) => Str::afterLast($supervisor->name, ':') === self::SUPERVISOR && array_sum($supervisor->processes ?? []) > 0)
            ->map(fn ($supervisor) => $supervisor->options['queue'])
            ->unique()
            ->values()
            ->all();
    }

    private static function resultsKey(WooCommerceUser $wooCommerceUser): string
    {
        return 'woo_server_probe_'.$wooCommerceUser->id;
    }

    /**
     * The address this server leaves from, read off a UDP socket: connecting one sends nothing.
     */
    private static function serverIp(): ?string
    {
        $socket = @stream_socket_client('udp://1.1.1.1:53');

        return $socket ? Str::beforeLast(stream_socket_get_name($socket, false), ':') : null;
    }
}

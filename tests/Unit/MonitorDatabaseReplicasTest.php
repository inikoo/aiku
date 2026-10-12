<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 16:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use App\Actions\DevOps\MonitorDatabaseReplicas;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

function databaseReplica(string $slot, array $overrides = []): object
{
    return (object) [
        'slot_name'          => $slot,
        'active'             => true,
        'wal_status'         => 'reserved',
        'retained_bytes'     => 12 * 1024 * 1024,
        'client_addr'        => '10.0.0.2',
        'state'              => 'streaming',
        'unapplied_bytes'    => 0,
        ...$overrides,
    ];
}

beforeEach(function () {
    Http::fake();
    Config::set('services.discord.webhook_url', 'https://discord.com/api/webhooks/1/A');
    Cache::forget('monitor:database_replicas:alerted');
});

afterEach(function () {
    MonitorDatabaseReplicas::clearFake();
    Cache::forget('monitor:database_replicas:alerted');
});

test('connected replicas that keep up are healthy, including a delayed one with hours of changes waiting', function () {
    $report = MonitorDatabaseReplicas::make()->report(collect([
        databaseReplica('replica_litio'),
        databaseReplica('replica_helio_delayed', ['unapplied_bytes' => 45 * 1024 ** 3]),
    ]), MonitorDatabaseReplicas::EXPECTED_SLOTS);

    expect(collect($report)->pluck('status')->all())->toBe(['healthy', 'healthy'])
        ->and(collect($report)->pluck('issue')->filter()->all())->toBe([])
        ->and($report[1]['label'])->toBe('12 hour delayed replica on helio')
        ->and($report[1]['unapplied_bytes'])->toBe(45 * 1024 ** 3);
});

test('a disconnected replica is broken and says how much the primary is holding for it', function () {
    $report = MonitorDatabaseReplicas::make()->report(collect([
        databaseReplica('replica_helio_delayed', ['active' => false, 'retained_bytes' => 30 * 1024 ** 3, 'client_addr' => null, 'state' => null]),
    ]), []);

    expect($report[0]['status'])->toBe('broken')
        ->and($report[0]['issue'])->toContain('replica_helio_delayed')->toContain('disconnected')->toContain('30.0 GB');
});

test('a replica the primary cut off, one about to be, and one far behind are told apart', function () {
    $report = collect(MonitorDatabaseReplicas::make()->report(collect([
        databaseReplica('cut_off', ['active' => false, 'wal_status' => 'lost']),
        databaseReplica('nearly', ['wal_status' => 'unreserved', 'retained_bytes' => 99 * 1024 ** 3]),
        databaseReplica('behind', ['retained_bytes' => 60 * 1024 ** 3]),
        databaseReplica('at_the_limit', ['retained_bytes' => MonitorDatabaseReplicas::BEHIND_WARNING_BYTES]),
    ]), []))->keyBy('slot');

    expect($report['cut_off']['status'])->toBe('broken')
        ->and($report['cut_off']['issue'])->toContain('copied again')
        ->and($report['nearly']['status'])->toBe('warning')
        ->and($report['nearly']['issue'])->toContain('about to be cut off')
        ->and($report['behind']['status'])->toBe('warning')
        ->and($report['behind']['issue'])->toContain('60.0 GB')
        ->and($report['at_the_limit']['status'])->toBe('healthy');
});

test('an expected replica whose slot is gone is reported, and none is expected outside production', function () {
    $monitor = MonitorDatabaseReplicas::make();

    $report = $monitor->report(collect([databaseReplica('replica_litio')]), MonitorDatabaseReplicas::EXPECTED_SLOTS);

    expect($report)->toHaveCount(2)
        ->and($report[1]['slot'])->toBe('replica_helio_delayed')
        ->and($report[1]['status'])->toBe('broken')
        ->and($report[1]['issue'])->toContain('no longer exists')
        ->and($monitor->report(collect()))->toBe([]);
});

test('the same problem alerts Discord once, a different one alerts again, and recovery re-arms it', function () {
    $disconnected = collect([databaseReplica('replica_helio_delayed', ['active' => false])]);
    $cutOff       = collect([databaseReplica('replica_helio_delayed', ['active' => false, 'wal_status' => 'lost'])]);

    MonitorDatabaseReplicas::partialMock()->shouldReceive('replicas')->andReturn($disconnected, $disconnected, $cutOff, collect(), $disconnected);

    expect(MonitorDatabaseReplicas::run())->toHaveCount(1);
    MonitorDatabaseReplicas::run();
    Http::assertSentCount(1);
    Http::assertSent(fn ($request) => str_contains($request['content'], 'replica_helio_delayed') && str_contains($request['content'], 'disconnected'));

    MonitorDatabaseReplicas::run();
    Http::assertSentCount(2);

    expect(MonitorDatabaseReplicas::run())->toBe([]);
    Http::assertSentCount(2);

    MonitorDatabaseReplicas::run();
    Http::assertSentCount(3);
});

test('a primary whose replication state cannot be read is itself an alert', function () {
    MonitorDatabaseReplicas::partialMock()->shouldReceive('replicas')->andThrow(new RuntimeException('permission denied for view'));

    expect(MonitorDatabaseReplicas::run()[0])->toContain('cannot be read')->toContain('permission denied');
    Http::assertSentCount(1);
});

<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 16:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use App\Actions\DevOps\MonitorDatabaseReplicas;
use Carbon\CarbonImmutable;
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
    Cache::forget(MonitorDatabaseReplicas::PGBACKREST_CACHE_KEY);
    Cache::forget(MonitorDatabaseReplicas::PROBLEMS_CACHE_KEY);
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

test('the delayed replica disconnected by a primary restart in the last 13 hours is catching up, not broken, and does not alert', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 05:00:00 UTC'));
    $disconnected = ['active' => false, 'retained_bytes' => 10 * 1024 ** 3, 'client_addr' => null, 'state' => null];

    $report = collect(MonitorDatabaseReplicas::make()->report(collect([
        databaseReplica('replica_helio_delayed', [...$disconnected, 'primary_started_at' => '2026-10-20 01:59:26+00']),
        databaseReplica('replica_litio', [...$disconnected, 'primary_started_at' => '2026-10-20 01:59:26+00']),
    ]), []))->keyBy('slot');

    expect($report['replica_helio_delayed']['status'])->toBe('catching_up')
        ->and($report['replica_helio_delayed']['issue'])->toBeNull()
        ->and($report['replica_helio_delayed']['note'])->toContain('01:59 UTC')->toContain('around 13:59 UTC')->toContain('10.0 GB')
        ->and($report['replica_litio']['status'])->toBe('broken');

    $this->travelTo(CarbonImmutable::parse('2026-10-20 15:00:00 UTC'));
    $late = MonitorDatabaseReplicas::make()->report(collect([
        databaseReplica('replica_helio_delayed', [...$disconnected, 'primary_started_at' => '2026-10-20 01:59:26+00']),
    ]), []);

    expect($late[0]['status'])->toBe('broken')
        ->and($late[0]['note'])->toBeNull();
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

    $mock = MonitorDatabaseReplicas::partialMock();
    $mock->shouldReceive('replicas')->andReturn($disconnected, $disconnected, $cutOff, collect(), $disconnected);
    $mock->shouldReceive('archiver')->andReturn((object) ['archive_mode' => 'off', 'last_archived_time' => null, 'last_failed_time' => null]);

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

test('WAL archiving is quiet while off or recent, and alerts when it stalls or fails', function () {
    $monitor  = MonitorDatabaseReplicas::make();
    $archiver = fn (string $mode, ?string $archived, ?string $failed = null) => (object) ['archive_mode' => $mode, 'last_archived_time' => $archived, 'last_failed_time' => $failed];

    $this->travelTo('2026-10-20 10:00:00');

    expect($monitor->archiveIssues($archiver('off', null)))->toBe([])
        ->and($monitor->archiveIssues($archiver('on', '2026-10-20 09:50:00', '2026-10-20 09:55:00')))->toBe([])
        ->and($monitor->archiveIssues($archiver('on', '2026-10-20 09:40:00'))[0])->toContain('since 20 Oct 09:40 UTC')->not->toContain('failed')
        ->and($monitor->archiveIssues($archiver('on', '2026-10-20 09:40:00', '2026-10-20 09:59:00'))[0])->toContain('last attempts failed')
        ->and($monitor->archiveIssues($archiver('on', null))[0])->toContain('No WAL has reached Cloudflare R2 ever');
});

test('a stalled WAL archive alerts Discord alongside healthy replicas', function () {
    $mock = MonitorDatabaseReplicas::partialMock();
    $mock->shouldReceive('replicas')->andReturn(collect());
    $mock->shouldReceive('archiver')->andReturn((object) ['archive_mode' => 'on', 'last_archived_time' => now()->subHour()->toDateTimeString(), 'last_failed_time' => null, 'archived_count' => 10, 'failed_count' => 0]);
    Cache::put(MonitorDatabaseReplicas::PGBACKREST_CACHE_KEY, ['received_at' => now()->toIso8601String(), 'stanzas' => [[
        'name' => 'aiku', 'status_code' => 0, 'status_message' => 'ok', 'backup_running' => false,
        'backups' => [['label' => 'F', 'type' => 'full', 'started_at' => now()->subHours(2)->timestamp, 'finished_at' => now()->subHour()->timestamp, 'size' => 1, 'repo_size' => 1, 'repo_delta' => 1, 'error' => false]],
    ]]]);

    expect(MonitorDatabaseReplicas::run())->toHaveCount(1);
    Http::assertSent(fn ($request) => str_contains($request['content'], 'Cloudflare R2'));
});

test('the backup report flags a silent litio, a missing backup and a stale one, and stays quiet while archive_mode is off', function () {
    $monitor  = MonitorDatabaseReplicas::make();
    $archiver = (object) ['archive_mode' => 'on', 'last_archived_time' => '2026-10-20 09:59:00', 'last_failed_time' => null, 'archived_count' => 900, 'failed_count' => 0];
    $report   = fn (string $receivedAt, array $backups, int $statusCode = 0) => ['received_at' => $receivedAt, 'stanzas' => [[
        'name' => 'aiku', 'status_code' => $statusCode, 'status_message' => 'no valid backups', 'backup_running' => false, 'backups' => $backups,
    ]]];
    $backup = fn (string $label, string $type, string $finishedAt) => [
        'label' => $label, 'type' => $type, 'started_at' => CarbonImmutable::parse($finishedAt)->subMinutes(30)->timestamp, 'finished_at' => CarbonImmutable::parse($finishedAt)->timestamp,
        'size' => 1, 'repo_size' => 1, 'repo_delta' => 1, 'error' => false,
    ];

    $this->travelTo('2026-10-20 10:00:00');

    $healthy = $monitor->backupReport($archiver, $report('2026-10-20 09:55:00', [$backup('F', 'full', '2026-10-17 12:45:00'), $backup('I', 'incr', '2026-10-20 02:10:00')]));
    expect($healthy['issues'])->toBe([])
        ->and($healthy['backups'][0]['label'])->toBe('I')
        ->and($healthy['backups'][0]['duration_seconds'])->toBe(1800)
        ->and($healthy['restore_from'])->toBe(CarbonImmutable::parse('2026-10-17 12:45:00')->toIso8601String());

    expect($monitor->backupReport($archiver, null)['backup_issue'])->toContain('has not reported')
        ->and($monitor->backupReport($archiver, $report('2026-10-20 09:20:00', []))['backup_issue'])->toContain('since 20 Oct 09:20 UTC')
        ->and($monitor->backupReport($archiver, $report('2026-10-20 09:55:00', [], 2))['backup_issue'])->toContain('no pgBackRest backup')
        ->and($monitor->backupReport($archiver, $report('2026-10-20 09:55:00', [$backup('F', 'full', '2026-10-19 07:00:00')]))['backup_issue'])->toContain('19 Oct 07:00 UTC')
        ->and($monitor->backupReport((object) [...(array) $archiver, 'archive_mode' => 'off'], null)['issues'])->toBe([]);
});

test('the problems found are kept for the menu badge', function () {
    $mock = MonitorDatabaseReplicas::partialMock();
    $mock->shouldReceive('replicas')->andReturn(collect([databaseReplica('replica_helio_delayed', ['active' => false])]));
    $mock->shouldReceive('archiver')->andReturn((object) ['archive_mode' => 'off', 'last_archived_time' => null, 'last_failed_time' => null, 'archived_count' => 0, 'failed_count' => 0]);

    MonitorDatabaseReplicas::run();

    expect(Cache::get(MonitorDatabaseReplicas::PROBLEMS_CACHE_KEY))->toHaveCount(1)
        ->and(Cache::get(MonitorDatabaseReplicas::PROBLEMS_CACHE_KEY)[0])->toContain('disconnected');
});

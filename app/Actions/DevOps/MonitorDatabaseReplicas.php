<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 16:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\DevOps;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Watches the replicas of the production database from the primary (INI-071).
 * A replica that stops is silent: the primary keeps up to 100 GB of changes for
 * it, then cuts it off, and from then on the 12 hour delayed replica on helio,
 * the only way to undo a bad DELETE, has to be copied again before it protects
 * anything. Asked from the primary so a dead replica host is noticed too.
 */
class MonitorDatabaseReplicas
{
    use AsAction;

    public const int BEHIND_WARNING_BYTES = 50 * 1024 * 1024 * 1024;

    /** @var array<string, string> */
    public const array EXPECTED_SLOTS = [
        'replica_litio'         => 'Live replica on litio',
        'replica_helio_delayed' => '12 hour delayed replica on helio',
    ];

    public string $commandSignature = 'monitor:database_replicas';
    public string $commandDescription = 'Alert Discord when a database replica is disconnected, cut off or missing';

    /**
     * @return array<int, string> list of problems found
     */
    public function handle(?Command $command = null): array
    {
        Nightwatch::dontSample();

        try {
            $report = $this->report();
        } catch (\Exception $e) {
            $issues = ['The replication state of the primary cannot be read: '.$e->getMessage()];
            $this->alert($issues, 'unreadable', $command);

            return $issues;
        }

        $issues = collect($report)->pluck('issue')->filter()->values()->all();

        if ($issues) {
            $this->alert($issues, md5(preg_replace('/[\d.]+ GB/', '', implode('|', $issues))), $command);
        } else {
            Cache::forget($this->throttleKey());
            $command?->info(count($report).' database replicas healthy');
        }

        return $issues;
    }

    public function asCommand(Command $command): int
    {
        return $this->handle($command) === [] ? 0 : 1;
    }

    /**
     * @return Collection<int, object{slot_name: string, active: bool, wal_status: string|null, retained_bytes: int|null, client_addr: string|null, state: string|null, unapplied_bytes: int|null}>
     */
    public function replicas(): Collection
    {
        return collect(DB::select("
            select s.slot_name,
                   s.active,
                   s.wal_status,
                   pg_wal_lsn_diff(pg_current_wal_lsn(), s.restart_lsn)::bigint as retained_bytes,
                   host(r.client_addr) as client_addr,
                   r.state,
                   pg_wal_lsn_diff(r.flush_lsn, r.replay_lsn)::bigint as unapplied_bytes
            from pg_replication_slots s
            left join pg_stat_replication r on r.pid = s.active_pid
            where s.slot_type = 'physical'
            order by s.slot_name
        "));
    }

    /**
     * @param  array<string, string>|null  $expectedSlots
     * @return array<int, array{slot: string, label: string, status: string, issue: string|null, active: bool, wal_status: string|null, retained_bytes: int|null, client_addr: string|null, state: string|null, unapplied_bytes: int|null}>
     */
    public function report(?Collection $replicas = null, ?array $expectedSlots = null): array
    {
        $replicas      ??= $this->replicas();
        $expectedSlots ??= app()->isProduction() ? self::EXPECTED_SLOTS : [];

        $report = $replicas->map(function (object $replica) use ($expectedSlots) {
            $label         = $expectedSlots[$replica->slot_name] ?? $replica->slot_name;
            $retainedBytes = $replica->retained_bytes === null ? null : (int) $replica->retained_bytes;
            $retained      = $this->gigabytes($retainedBytes);

            [$status, $issue] = match (true) {
                $replica->wal_status === 'lost'               => ['broken', "$label (`$replica->slot_name`) fell too far behind and the primary cut it off. It has to be copied again from the primary."],
                !$replica->active                             => ['broken', "$label (`$replica->slot_name`) is disconnected. The primary is keeping $retained of changes for it and cuts it off at 100 GB."],
                $replica->wal_status === 'unreserved'         => ['warning', "$label (`$replica->slot_name`) is $retained behind and about to be cut off by the primary."],
                $retainedBytes > self::BEHIND_WARNING_BYTES   => ['warning', "$label (`$replica->slot_name`) is connected but has not received $retained of changes."],
                default                                       => ['healthy', null],
            };

            return [
                'slot'               => $replica->slot_name,
                'label'              => $label,
                'status'             => $status,
                'issue'              => $issue,
                'active'             => (bool) $replica->active,
                'wal_status'         => $replica->wal_status,
                'retained_bytes'     => $retainedBytes,
                'client_addr'        => $replica->client_addr,
                'state'              => $replica->state,
                'unapplied_bytes'    => $replica->unapplied_bytes === null ? null : (int) $replica->unapplied_bytes,
            ];
        });

        foreach (array_diff_key($expectedSlots, $replicas->keyBy('slot_name')->all()) as $slot => $label) {
            $report->push([
                'slot'               => $slot,
                'label'              => $label,
                'status'             => 'broken',
                'issue'              => "$label (`$slot`) no longer exists on the primary. It has to be copied again from the primary.",
                'active'             => false,
                'wal_status'         => null,
                'retained_bytes'     => null,
                'client_addr'        => null,
                'state'              => null,
                'unapplied_bytes'    => null,
            ]);
        }

        return $report->values()->all();
    }

    protected function gigabytes(?int $bytes): string
    {
        return number_format(($bytes ?? 0) / 1024 ** 3, 1).' GB';
    }

    protected function throttleKey(): string
    {
        return 'monitor:database_replicas:alerted';
    }

    /**
     * @param  array<int, string>  $issues
     */
    protected function alert(array $issues, string $fingerprint, ?Command $command = null): void
    {
        foreach ($issues as $issue) {
            $command?->error($issue);
        }

        // ponytail: a stopped replica stays stopped, so the same set of problems
        // alerts once every 6 hours; a new or different problem alerts at once.
        if (Cache::get($this->throttleKey()) === $fingerprint) {
            return;
        }
        Cache::put($this->throttleKey(), $fingerprint, now()->addHours(6));

        $webhookUrl = config('services.discord.webhook_url');

        if (!$webhookUrl) {
            $command?->error('Discord webhook URL is not configured. Please set it in config/services.php or .env');

            return;
        }

        try {
            Http::post($webhookUrl, [
                'content' => "🛟 **Database Replica Alert** 🛟   <@&1164019425154969600>\n\n- ".implode("\n- ", $issues)
                    ."\n\nUntil it is back, a mistake on production cannot be undone from the delayed replica. See the Devops dashboard.",
            ]);
        } catch (\Exception $e) {
            $command?->error('Failed to send Discord notification: '.$e->getMessage());
        }
    }
}

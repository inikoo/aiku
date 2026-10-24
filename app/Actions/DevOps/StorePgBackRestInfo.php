<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 17 Oct 2026 21:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\DevOps;

use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Receives `pgbackrest info --output=json` from litio (INI-113, INI-132), which holds the
 * repository keys the app cannot read, and keeps the newest copy for the Devops dashboard and
 * MonitorDatabaseReplicas.
 */
class StorePgBackRestInfo
{
    use AsAction;

    /**
     * @param  array<int, array<string, mixed>>  $stanzas
     */
    public function handle(array $stanzas): void
    {
        Cache::forever(MonitorDatabaseReplicas::PGBACKREST_CACHE_KEY, [
            'received_at' => now()->toIso8601String(),
            'stanzas'     => array_map(fn (array $stanza) => [
                'name'           => $stanza['name'],
                'status_code'    => $stanza['status']['code'] ?? null,
                'status_message' => $stanza['status']['message'] ?? null,
                'backup_running' => (bool) ($stanza['status']['lock']['backup']['held'] ?? false),
                'repos'          => array_map(fn (array $repo) => [
                    'key'            => (int) $repo['key'],
                    'status_code'    => $repo['status']['code'] ?? null,
                    'status_message' => $repo['status']['message'] ?? null,
                ], $stanza['repo'] ?? []),
                'backups'        => array_map(fn (array $backup) => [
                    'repo'        => (int) ($backup['database']['repo-key'] ?? 1),
                    'label'       => $backup['label'],
                    'type'        => $backup['type'],
                    'started_at'  => $backup['timestamp']['start'] ?? null,
                    'finished_at' => $backup['timestamp']['stop'] ?? null,
                    'size'        => $backup['info']['size'] ?? null,
                    'repo_size'   => $backup['info']['repository']['size'] ?? null,
                    'repo_delta'  => $backup['info']['repository']['delta'] ?? null,
                    'error'       => (bool) ($backup['error'] ?? false),
                ], $stanza['backup'] ?? []),
            ], $stanzas),
        ]);
    }

    public function rules(): array
    {
        return [
            '*'                => ['array'],
            '*.name'           => ['required', 'string', 'max:64'],
            '*.status'         => ['required', 'array'],
            '*.backup'         => ['present', 'array', 'max:200'],
            '*.backup.*.label' => ['required', 'string', 'max:64'],
            '*.backup.*.type'  => ['required', 'string', 'in:full,diff,incr'],
            '*.backup.*.database.repo-key' => ['nullable', 'integer', 'min:1'],
            '*.repo'           => ['sometimes', 'array', 'max:4'],
            '*.repo.*.key'     => ['required', 'integer', 'min:1'],
        ];
    }

    public function asController(ActionRequest $request): array
    {
        $this->handle(array_values($request->all()));

        return ['ok' => true];
    }
}

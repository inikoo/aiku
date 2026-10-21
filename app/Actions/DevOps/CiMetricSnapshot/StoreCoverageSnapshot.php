<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 10:52:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\DevOps\CiMetricSnapshot;

use App\Models\DevOps\CiMetricSnapshot;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCoverageSnapshot
{
    use AsAction;

    /**
     * @param  array{run_id?: int|null, head_sha?: string|null, measured_at?: string|null, directories: array<string, array{0: int, 1: int}>}  $modelData
     */
    public function handle(array $modelData): CiMetricSnapshot
    {
        $modules = [];
        foreach ($modelData['directories'] as $directory => [$lines, $hits]) {
            $module                    = self::moduleOf($directory);
            $modules[$module]['lines'] = ($modules[$module]['lines'] ?? 0) + $lines;
            $modules[$module]['hits']  = ($modules[$module]['hits'] ?? 0) + $hits;
        }
        ksort($modules);

        $lines = array_sum(array_column($modules, 'lines'));
        $hits  = array_sum(array_column($modules, 'hits'));

        return CiMetricSnapshot::updateOrCreate(
            ['type' => CiMetricSnapshot::COVERAGE, 'head_sha' => $modelData['head_sha'] ?? null],
            [
                'github_run_id' => $modelData['run_id'] ?? null,
                'measured_at'   => $modelData['measured_at'] ?? now(),
                'summary'       => ['lines' => $lines, 'hits' => $hits, 'coverage' => self::percentage($hits, $lines)],
                'breakdown'     => $modules,
            ]
        );
    }

    public static function moduleOf(string $directory): string
    {
        $segments = array_values(array_filter(explode('/', $directory)));
        if (($segments[0] ?? null) === 'app') {
            array_shift($segments);
        }
        array_shift($segments);
        if (in_array($segments[0] ?? null, ['Controllers', 'Resources', 'Requests', 'Middleware'], true)) {
            array_shift($segments);
        }

        return $segments[0] ?? 'Shared';
    }

    public static function percentage(int $hits, int $lines): ?float
    {
        return $lines > 0 ? round($hits / $lines * 100, 2) : null;
    }

    public function rules(): array
    {
        return [
            'run_id'        => ['nullable', 'integer', 'min:1'],
            'head_sha'      => ['nullable', 'string', 'max:40'],
            'measured_at'   => ['nullable', 'date'],
            'directories'   => ['required', 'array', 'min:1'],
            'directories.*' => ['array', 'size:2'],
            'directories.*.*' => ['integer', 'min:0'],
        ];
    }

    public function asController(ActionRequest $request): array
    {
        $snapshot = $this->handle($request->validated());

        return ['ok' => true, 'coverage' => $snapshot->summary['coverage']];
    }
}

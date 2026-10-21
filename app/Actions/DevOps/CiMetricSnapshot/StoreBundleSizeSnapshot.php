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

class StoreBundleSizeSnapshot
{
    use AsAction;

    /**
     * @param  array{run_id?: int|null, head_sha?: string|null, bundles: array<string, array{files: int, js_bytes: int, css_bytes: int, gzip_bytes: int, largest?: list<array{file: string, bytes: int, gzip_bytes: int}>}>}  $modelData
     */
    public function handle(array $modelData): CiMetricSnapshot
    {
        $bundles = $modelData['bundles'];
        ksort($bundles);

        return CiMetricSnapshot::updateOrCreate(
            ['type' => CiMetricSnapshot::BUNDLE_SIZE, 'head_sha' => $modelData['head_sha'] ?? null],
            [
                'github_run_id' => $modelData['run_id'] ?? null,
                'measured_at'   => now(),
                'summary'       => [
                    'bytes'      => array_sum(array_map(fn (array $bundle) => $bundle['js_bytes'] + $bundle['css_bytes'], $bundles)),
                    'gzip_bytes' => array_sum(array_column($bundles, 'gzip_bytes')),
                ],
                'breakdown'     => $bundles,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'run_id'                        => ['nullable', 'integer', 'min:1'],
            'head_sha'                      => ['nullable', 'string', 'max:40'],
            'bundles'                       => ['required', 'array', 'min:1'],
            'bundles.*.files'               => ['required', 'integer', 'min:0'],
            'bundles.*.js_bytes'            => ['required', 'integer', 'min:0'],
            'bundles.*.css_bytes'           => ['required', 'integer', 'min:0'],
            'bundles.*.gzip_bytes'          => ['required', 'integer', 'min:0'],
            'bundles.*.largest'             => ['sometimes', 'array', 'max:10'],
            'bundles.*.largest.*.file'      => ['required', 'string', 'max:255'],
            'bundles.*.largest.*.bytes'     => ['required', 'integer', 'min:0'],
            'bundles.*.largest.*.gzip_bytes' => ['required', 'integer', 'min:0'],
        ];
    }

    public function asController(ActionRequest $request): array
    {
        $this->handle($request->validated());

        return ['ok' => true];
    }
}

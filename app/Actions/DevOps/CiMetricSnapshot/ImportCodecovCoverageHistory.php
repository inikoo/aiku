<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 10:52:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\DevOps\CiMetricSnapshot;

use App\Models\DevOps\CiMetricSnapshot;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

class ImportCodecovCoverageHistory
{
    use AsAction;

    public string $commandSignature = 'ci-metrics:import-codecov {--days=365 : How far back to import}';

    public function handle(int $days = 365): int
    {
        [$owner, $repo] = explode('/', config('services.github.repo'));
        $commitsUrl     = "https://api.codecov.io/api/v2/github/$owner/repos/$repo/commits/";
        $since          = now()->subDays($days);
        $imported       = 0;

        for ($page = 1; $page <= 100; $page++) {
            $commits = $this->codecov()->get($commitsUrl, ['branch' => 'main', 'page_size' => 100, 'page' => $page])->throw()->json('results', []);

            foreach ($commits as $commit) {
                if (Carbon::parse($commit['timestamp'])->lt($since)) {
                    return $imported;
                }
                if (!$commit['totals'] || CiMetricSnapshot::where('type', CiMetricSnapshot::COVERAGE)->where('head_sha', $commit['commitid'])->exists()) {
                    continue;
                }

                $directories = [];
                foreach ($this->codecov()->get($commitsUrl.$commit['commitid'].'/')->throw()->json('report.files', []) as $file) {
                    $directory                  = dirname($file['name']);
                    $directories[$directory][0] = ($directories[$directory][0] ?? 0) + $file['totals']['lines'];
                    $directories[$directory][1] = ($directories[$directory][1] ?? 0) + $file['totals']['hits'];
                }

                if ($directories) {
                    StoreCoverageSnapshot::run(['head_sha' => $commit['commitid'], 'measured_at' => $commit['timestamp'], 'directories' => $directories]);
                    $imported++;
                }
            }

            if (count($commits) < 100) {
                break;
            }
        }

        return $imported;
    }

    private function codecov(): PendingRequest
    {
        return Http::connectTimeout(5)->timeout(60)->retry(3, 5000)->acceptJson();
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $command->info('Imported '.$this->handle((int) $command->option('days')).' coverage snapshots from Codecov.');

        return 0;
    }
}

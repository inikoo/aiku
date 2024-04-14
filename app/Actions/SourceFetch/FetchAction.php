<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 Apr 2024 15:24:40 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch;

use App\Actions\Helpers\Fetch\UpdateFetch;
use App\Actions\Traits\WithOrganisationSource;
use App\Enums\Helpers\FetchRecord\FetchRecordTypeEnum;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Services\Organisation\AuroraOrganisationService;
use App\Services\Organisation\SourceOrganisationService;
use App\Services\Organisation\WowsbarOrganisationService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\LazyCollection;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchAction
{
    use AsAction;
    use WithOrganisationSource;

    protected int $counter = 0;
    protected ?ProgressBar $progressBar;

    protected int $number_stores     = 0;
    protected int $number_updates    = 0;
    protected int $number_no_changes = 0;
    protected int $number_errors     = 0;

    protected AuroraOrganisationService|WowsbarOrganisationService|null $organisationSource = null;

    protected int $hydrateDelay = 0;

    protected ?Shop $shop;
    protected array $with;
    protected bool $onlyNew  = false;
    protected bool $fetchAll = false;


    public function __construct()
    {
        $this->progressBar = null;
        $this->shop        = null;
        $this->with        = [];
    }


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): Model|array|null
    {
        return null;
    }

    public function getModelsQuery(): ?Builder
    {
        return null;
    }

    public function fetchAll(SourceOrganisationService $organisationSource, Command $command = null): void
    {
        $this->getModelsQuery()->chunk(10000, function ($chunkedData) use ($command, $organisationSource) {
            foreach ($chunkedData as $auroraData) {
                if ($command && $command->getOutput()->isDebug()) {
                    $command->line("Fetching: ".$auroraData->{'source_id'});
                }
                $model = $this->handle($organisationSource, $auroraData->{'source_id'});
                unset($model);
                $this->progressBar?->advance();
            }
        });
    }

    public function count(): ?int
    {
        return null;
    }

    public function reset(): void
    {
    }

    protected function getOrganisations(Command $command): LazyCollection
    {
        return Organisation::query()
            ->when($command->argument('organisations'), function ($query) use ($command) {
                $query->whereIn('slug', $command->argument('organisations'));
            })
            ->cursor();
    }

    public function asCommand(Command $command): int
    {
        $this->hydrateDelay = 120;

        $organisations = $this->getOrganisations($command);
        $exitCode      = 0;

        foreach ($organisations as $organisation) {
            $result = $this->processOrganisation($command, $organisation);

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }


    public function recordError($organisationSource, $e, $modelData, $modelType = null, $errorOn = null): void
    {
        $this->number_errors++;
        UpdateFetch::run($organisationSource->fetch, ['number_errors' => $this->number_errors]);
        $organisationSource->fetch->records()->create([
            'model_data' => $modelData,
            'data'       => $e->getMessage(),
            'type'       => FetchRecordTypeEnum::ERROR,
            'source_id'  => $modelData['source_id'],
            'model_type' => $modelType,
            'error_on'   => $errorOn
        ]);
        print_r($modelData);
        print_r($e->getMessage());
    }

    public function recordChange($organisationSource, $wasChanged): void
    {
        if ($wasChanged) {
            $this->number_updates++;
            UpdateFetch::run($organisationSource->fetch, ['number_updates' => $this->number_updates]);
        } else {
            $this->number_no_changes++;
            UpdateFetch::run($organisationSource->fetch, ['number_no_changes' => $this->number_no_changes]);
        }
    }

    public function recordNew($organisationSource): void
    {
        $this->number_stores++;
        UpdateFetch::run($organisationSource->fetch, ['number_stores' => $this->number_stores]);
    }

}

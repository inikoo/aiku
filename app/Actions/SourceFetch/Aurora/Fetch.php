<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 00:35:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

/** @noinspection PhpUnused */

namespace App\Actions\SourceFetch\Aurora;

use App\Jobs\Middleware\InitialiseSourceOrganisation;
use App\Managers\Organisation\SourceOrganisationManager;
use App\Models\Organisations\Organisation;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;
use Symfony\Component\Console\Helper\ProgressBar;


class Fetch
{
    use AsAction;


    protected ?ProgressBar $progressBar;

    public function __construct()
    {
        $this->progressBar = null;
    }

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Model
    {
        return null;
    }

    function getModelsQuery(): ?Builder
    {
        return null;
    }

    public function fetchAll(SourceOrganisationService $organisationSource): void
    {
        foreach ($this->getModelsQuery()->get() as $auroraData) {
            $this->handle($organisationSource, $auroraData->{'source_id'});
        }
    }

    public function fetchSome(SourceOrganisationService $organisationSource, array $organisationIds): void
    {
        foreach ($organisationIds as $sourceID) {
            $this->handle($organisationSource, $sourceID);
        }
    }

    public function count(): ?int
    {
        return null;
    }


    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function asCommand(Command $command): void
    {
        $organisation = Organisation::where('code', $command->argument('organisation_code'))->first();
        if (!$organisation) {
            $command->error('Organisation not found');
            return;
        }

        $organisationSource = app(SourceOrganisationManager::class)->make($organisation->type);
        $organisationSource->initialisation($organisation);

        if ($command->argument('organisation_source_id')) {
            $this->handle($organisationSource, $command->argument('organisation_source_id'));
        } else {
            $this->progressBar = $command->getOutput()->createProgressBar($this->count());
            $this->progressBar->setFormat('debug');
            $this->progressBar->start();

            $this->fetchAll($organisationSource);
            $this->progressBar->finish();
        }
    }

    public function asJob(SourceOrganisationService $organisationSource, ?array $organisationIds = null): void
    {
        if (is_array($organisationIds)) {
            $this->fetchSome($organisationSource, $organisationIds);
        } else {
            $this->getModelsQuery()
                ->chunk(100, function ($organisationIds) use ($organisationSource) {
                    $this->dispatch($organisationSource, $organisationIds->pluck('source_id')->all());
                });
        }
    }

    public function getJobMiddleware(): array
    {
        return [new InitialiseSourceOrganisation()];
    }

    public function configureJob(JobDecorator $job): void
    {
        $job->onQueue('fetches')
            ->setTries(5)
            ->setMaxExceptions(3)
            ->setTimeout(1800);
    }

}


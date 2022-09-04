<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 00:35:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

/** @noinspection PhpUnused */

namespace App\Actions\SourceFetch\Aurora;

use App\Managers\Organisation\SourceOrganisationManager;
use App\Models\Organisations\Organisation;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Console\Helper\ProgressBar;


class FetchModel
{
    use AsAction;


    protected ?ProgressBar $progressBar;

    public function __construct()
    {
        $this->progressBar = null;
    }

    public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?Model
    {
        return null;
    }

    public function fetchAll(SourceOrganisationService $organisationSource): void
    {
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

}


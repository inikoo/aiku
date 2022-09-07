<?php /** @noinspection PhpUnused */

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 16:15:47 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;



use App\Managers\Organisation\SourceOrganisationManager;
use App\Models\Organisations\Organisation;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class FetchEssentialModels
{
    use AsAction;


    public string $commandSignature = 'fetch:essential-models {organisation_code}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource): void
    {
        FetchShop::dispatch($organisationSource);
        FetchEmployee::dispatch($organisationSource);
        Bus::chain([
                       FetchWarehouse::makeJob($organisationSource),
                       FetchWarehouseArea::makeJob($organisationSource),
                       FetchLocation::makeJob($organisationSource),
                       FetchStock::makeJob($organisationSource),
                   ])->dispatch();
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
        $this->handle($organisationSource);
    }


}

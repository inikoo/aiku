<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 13:36:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

/** @noinspection PhpUnused */


namespace App\Actions\SourceUpserts\Aurora\Mass;

use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Marketing\Shop\UpdateShop;
use App\Managers\Organisation\SourceOrganisationManager;
use App\Models\Marketing\Shop;
use App\Models\Organisations\Organisation;
use Exception;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property Organisation $organisation
 * @property Shop $shop
 */
class UpsertShopsFromSource
{
    use AsAction;
    use WithMassFromSourceCommand;

    public string $commandSignature = 'source-update:shops {organisation_code} {scopes?*}';


    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Exception
     */
    #[NoReturn] public function handle(Organisation $organisation, array|null $scopes = null): void
    {
        $this->organisation = $organisation;


        $validScopes = ['upsertShop'];

        $organisationSource = app(SourceOrganisationManager::class)->make($this->organisation->type);
        $organisationSource->initialisation($this->organisation);

        foreach (
            DB::connection('aurora')
                ->table('Store Dimension')
                ->select('Store Key')
                ->whereIn('Store Status', ['Normal','ClosingDown'])
                ->get() as $auroraData
        ) {
            $shopData = $organisationSource->fetchShop($auroraData->{'Store Key'});

            if ($scopes == null) {
                $scopes = $validScopes;
            }

            foreach ($scopes as $scope) {
                if (!method_exists($this, $scope)) {
                    throw new Exception("Scope $scope is not supported");
                }
                $this->{$scope}($shopData);
            }
        }
    }

    protected function upsertShop($shopData): void
    {
        if ($shop = Shop::where('organisation_source_id', $shopData['shop']['organisation_source_id'])
            ->where('organisation_id',$this->organisation->id)
            ->first()) {
            $res = UpdateShop::run($shop, $shopData['shop']);
        } else {
            $res = StoreShop::run($this->organisation, $shopData['shop']);
        }
        $this->shop = $res->model;
    }



}

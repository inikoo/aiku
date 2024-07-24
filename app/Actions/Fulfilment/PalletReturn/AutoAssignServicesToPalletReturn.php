<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jul 2024 00:03:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\FulfilmentTransaction\DeleteFulfilmentTransaction;
use App\Actions\Fulfilment\FulfilmentTransaction\StoreFulfilmentTransaction;
use App\Actions\Fulfilment\FulfilmentTransaction\UpdateFulfilmentTransaction;
use App\Actions\OrgAction;
use App\Models\Catalogue\Service;
use App\Models\Fulfilment\FulfilmentTransaction;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\StoredItem;

class AutoAssignServicesToPalletReturn extends OrgAction
{
    public function handle(PalletReturn  $palletReturn, Pallet|StoredItem $subject): PalletReturn
    {
        /** @var Service $service */
        $service=$palletReturn->fulfilment->shop->services()->where([
            ['is_auto_assign', true],
            ['auto_assign_trigger', class_basename($palletReturn)],
            ['auto_assign_subject', class_basename($subject)],
            ['auto_assign_subject_type', $subject->type]
        ])->first();

        if(!$service) {
            return $palletReturn;
        }

        $asset    =$service->asset;

        if($subject instanceof Pallet) {
            $quantity = $palletReturn->pallets()->where('pallets.type', $subject->type)->count();
        } else {
            $quantity = $palletReturn->storedItems()->where('stored_items.type', $subject->type)->count();
        }


        data_set($modelData, 'quantity', $quantity);
        data_set($modelData, 'is_auto_assign', true);


        /** @var FulfilmentTransaction $transaction */
        $transaction=$palletReturn->transactions()->where('asset_id', $asset->id)->first();

        if($quantity == 0) {
            if($transaction) {
                DeleteFulfilmentTransaction::run($transaction);
            }

            return $palletReturn;
        }


        if($transaction) {

            if($transaction->historic_asset_id!=$asset->current_historic_asset_id) {

                DeleteFulfilmentTransaction::run($transaction);
                data_set($modelData, 'historic_asset_id', $asset->current_historic_asset_id);
                StoreFulfilmentTransaction::make()->action($palletReturn, $modelData);
            } else {
                UpdateFulfilmentTransaction::make()->action($transaction, $modelData);
            }


        } else {
            data_set($modelData, 'historic_asset_id', $asset->current_historic_asset_id);

            StoreFulfilmentTransaction::make()->action($palletReturn, $modelData);

        }




        return $palletReturn;
    }



}

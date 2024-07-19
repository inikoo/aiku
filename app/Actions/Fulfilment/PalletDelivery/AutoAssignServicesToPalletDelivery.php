<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jul 2024 00:03:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\FulfilmentTransaction\DeleteFulfilmentTransaction;
use App\Actions\Fulfilment\FulfilmentTransaction\StoreFulfilmentTransaction;
use App\Actions\Fulfilment\FulfilmentTransaction\UpdateFulfilmentTransaction;
use App\Actions\OrgAction;
use App\Models\Catalogue\Service;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\FulfilmentTransaction;

class AutoAssignServicesToPalletDelivery extends OrgAction
{
    public function handle(PalletDelivery $palletDelivery, $subject, $previousType = null): PalletDelivery
    {
        if ($previousType) {
            // Update or delete the service associated with the previous type
            $previousService = $palletDelivery->fulfilment->shop->services()->where([
                ['is_auto_assign', true],
                ['auto_assign_trigger', class_basename($palletDelivery)],
                ['auto_assign_subject', class_basename($subject)],
                ['auto_assign_subject_type', $previousType]
            ])->first();

            if ($previousService) {
                $previousAsset = $previousService->asset;
                $previousTransaction = $palletDelivery->transactions()->where('asset_id', $previousAsset->id)->first();
                if ($previousTransaction) {
                    $previousQuantity = $palletDelivery->pallets()->where('type', $previousType)->count();
                    if ($previousQuantity == 0) {
                        DeleteFulfilmentTransaction::run($previousTransaction);
                    } else {
                        $previousModelData = ['quantity' => $previousQuantity];
                        UpdateFulfilmentTransaction::make()->action($previousTransaction, $previousModelData);
                    }
                }
            }
        }

        /** @var Service $service */
        $service = $palletDelivery->fulfilment->shop->services()->where([
            ['is_auto_assign', true],
            ['auto_assign_trigger', class_basename($palletDelivery)],
            ['auto_assign_subject', class_basename($subject)],
            ['auto_assign_subject_type', $subject->type]
        ])->first();

        if (!$service) {
            return $palletDelivery;
        }

        $asset = $service->asset;
        $quantity = $palletDelivery->pallets()->where('type', $subject->type)->count();
        $modelData = [];

        data_set($modelData, 'quantity', $quantity);
        data_set($modelData, 'is_auto_assign', true);

        /** @var FulfilmentTransaction $transaction */
        $transaction = $palletDelivery->transactions()->where('asset_id', $asset->id)->first();

        if ($quantity == 0) {
            if ($transaction) {
                DeleteFulfilmentTransaction::run($transaction);
            }
            return $palletDelivery;
        }

        if ($transaction) {
            if ($transaction->historic_asset_id != $asset->current_historic_asset_id) {
                DeleteFulfilmentTransaction::run($transaction);
                data_set($modelData, 'historic_asset_id', $asset->current_historic_asset_id);
                StoreFulfilmentTransaction::make()->action($palletDelivery, $modelData);
            } else {
                UpdateFulfilmentTransaction::make()->action($transaction, $modelData);
            }
        } else {
            data_set($modelData, 'historic_asset_id', $asset->current_historic_asset_id);
            StoreFulfilmentTransaction::make()->action($palletDelivery, $modelData);
        }

        return $palletDelivery;
    }


}

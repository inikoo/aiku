<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jul 2024 19:55:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentTransaction;

use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydrateTransactions;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydrateTransactions;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\FulfilmentTransaction\FulfilmentTransactionTypeEnum;
use App\Models\Catalogue\HistoricAsset;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\FulfilmentTransaction;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\ActionRequest;

class StoreFulfilmentTransaction extends OrgAction
{
    public function handle(PalletDelivery|PalletReturn $parent, HistoricAsset $historicAsset, array $modelData): FulfilmentTransaction
    {
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'fulfilment_id', $parent->fulfilment_id);
        data_set($modelData, 'fulfilment_customer_id', $parent->fulfilment_customer_id);
        data_set($modelData, 'historic_asset_id', $historicAsset->id);
        data_set($modelData, 'asset_id', $historicAsset->asset_id);

        if($historicAsset->model_type === 'Product') {
            data_set($modelData, 'type', FulfilmentTransactionTypeEnum::PRODUCT);
        } else {
            data_set($modelData, 'type', FulfilmentTransactionTypeEnum::SERVICE);
        }

        /** @var FulfilmentTransaction $fulfilmentTransaction */
        $fulfilmentTransaction= $parent->transactions()->create($modelData);

        if($fulfilmentTransaction->parent_type=='PalletDelivery') {
            PalletDeliveryHydrateTransactions::run($fulfilmentTransaction->parent);
        } else {
            PalletReturnHydrateTransactions::run($fulfilmentTransaction->parent);
        }

        return $fulfilmentTransaction;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function fromRetina(PalletDelivery|PalletReturn $parent, HistoricAsset $historicAsset, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($parent->fulfilment, $request);

        $this->handle($parent, $historicAsset, $this->validatedData);
    }

    public function asController(PalletDelivery|PalletReturn $parent, HistoricAsset $historicAsset, ActionRequest $actionRequest): void
    {
        $this->initialisationFromFulfilment($parent->fulfilment, $actionRequest);

        $this->handle($parent, $historicAsset, $this->validatedData);
    }



    public function action(PalletDelivery|PalletReturn $parent, HistoricAsset $historicAsset, array $modelData): FulfilmentTransaction
    {
        $this->asAction = true;

        $this->initialisationFromFulfilment($parent->fulfilment, $modelData);
        return $this->handle($parent, $historicAsset, $this->validatedData);
    }


}

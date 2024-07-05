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

        /** @var FulfilmentTransaction $palletDeliveryTransaction */
        $palletDeliveryTransaction= $parent->transactions()->create($modelData);

        if($palletDeliveryTransaction->parent_type=='PalletDelivery') {
            PalletDeliveryHydrateTransactions::run($palletDeliveryTransaction->parent);
        } else {
            PalletReturnHydrateTransactions::run($palletDeliveryTransaction->parent);
        }

        return $palletDeliveryTransaction;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function fromRetina(PalletDelivery $palletDelivery, HistoricAsset $historicAsset, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        $this->handle($palletDelivery, $historicAsset, $this->validatedData);
    }

    public function asController(PalletDelivery $palletDelivery, HistoricAsset $historicAsset, ActionRequest $actionRequest): void
    {
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $actionRequest);

        $this->handle($palletDelivery, $historicAsset, $this->validatedData);
    }



    public function action(PalletDelivery $palletDelivery, HistoricAsset $historicAsset, array $modelData): FulfilmentTransaction
    {
        $this->asAction = true;

        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $modelData);
        return $this->handle($palletDelivery, $historicAsset, $this->validatedData);
    }


}

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
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreFulfilmentTransaction extends OrgAction
{
    public function handle(PalletDelivery|PalletReturn $parent, array $modelData): FulfilmentTransaction
    {
        $historicAsset = HistoricAsset::find($modelData['historic_asset_id']);

        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'fulfilment_id', $parent->fulfilment_id);
        data_set($modelData, 'fulfilment_customer_id', $parent->fulfilment_customer_id);
        data_set($modelData, 'historic_asset_id', $historicAsset->id);
        data_set($modelData, 'asset_id', $historicAsset->asset_id);

        if ($historicAsset->model_type === 'Product') {
            data_set($modelData, 'type', FulfilmentTransactionTypeEnum::PRODUCT);
        } else {
            data_set($modelData, 'type', FulfilmentTransactionTypeEnum::SERVICE);
        }

        /** @var FulfilmentTransaction $fulfilmentTransaction */
        $fulfilmentTransaction = $parent->transactions()->create($modelData);

        if ($fulfilmentTransaction->parent_type == 'PalletDelivery') {
            PalletDeliveryHydrateTransactions::run($fulfilmentTransaction->parent);
        } else {
            PalletReturnHydrateTransactions::run($fulfilmentTransaction->parent);
        }

        return $fulfilmentTransaction;
    }

    public function rules(): array
    {
        return [
            'is_auto_assign'    => ['sometimes', 'boolean'],
            'quantity'          => ['required', 'numeric', 'min:0'],
            'historic_asset_id' => [
                'required',
                Rule::Exists('historic_assets', 'id')
                    ->where('organisation_id', $this->organisation->id)
            ]
        ];
    }

    public function fromRetinaInPalletDelivery(PalletDelivery $palletDelivery, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        $this->handle($palletDelivery, $this->validatedData);
    }

    public function fromRetinaInPalletReturn(PalletReturn $palletReturn, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);

        $this->handle($palletReturn, $this->validatedData);
    }

    public function inPalletDelivery(PalletDelivery $palletDelivery, ActionRequest $actionRequest): void
    {
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $actionRequest);

        $this->handle($palletDelivery, $this->validatedData);
    }

    public function inPalletReturn(PalletReturn $palletReturn, $historicAsset, ActionRequest $actionRequest): void
    {
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $actionRequest);
        $this->handle($palletReturn, $historicAsset, $this->validatedData);
    }


    public function action(PalletDelivery|PalletReturn $parent, array $modelData): FulfilmentTransaction
    {
        $this->asAction = true;

        $this->initialisationFromFulfilment($parent->fulfilment, $modelData);

        return $this->handle($parent, $this->validatedData);
    }


}

<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jul 2024 19:55:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\FulfilmentTransaction;

use App\Actions\Fulfilment\FulfilmentTransaction\SetClausesInFulfilmentTransaction;
use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydrateTransactions;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydrateTransactions;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\FulfilmentTransaction\FulfilmentTransactionTypeEnum;
use App\Models\Catalogue\HistoricAsset;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\FulfilmentTransaction;
use App\Models\Fulfilment\PalletReturn;
use App\Models\SysAdmin\Organisation;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreRetinaFulfilmentTransaction extends RetinaAction
{
    use WithActionUpdate;

    protected Organisation $organisation;

    public function handle(PalletDelivery|PalletReturn $parent, array $modelData): FulfilmentTransaction
    {

        data_set($modelData, 'tax_category_id', $parent->tax_category_id, overwrite:false);

        $historicAsset = HistoricAsset::find($modelData['historic_asset_id']);
        $net           = $modelData['quantity'] * $historicAsset->asset->price;

        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'fulfilment_id', $parent->fulfilment_id);
        data_set($modelData, 'fulfilment_customer_id', $parent->fulfilment_customer_id);
        data_set($modelData, 'historic_asset_id', $historicAsset->id);
        data_set($modelData, 'asset_id', $historicAsset->asset_id);
        data_set($modelData, 'gross_amount', $net);
        data_set($modelData, 'net_amount', $net);

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

        $fulfilmentTransaction->refresh();
        $this->update(
            $fulfilmentTransaction,
            [
            'grp_net_amount'   => $fulfilmentTransaction->net_amount * $fulfilmentTransaction->grp_exchange,
            'org_net_amount'   => $fulfilmentTransaction->net_amount * $fulfilmentTransaction->org_exchange
        ]
        );
        $fulfilmentTransaction->refresh();
        SetClausesInFulfilmentTransaction::run($fulfilmentTransaction);

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
        $this->initialisation($request);

        $this->handle($palletDelivery, $this->validatedData);
    }

    public function fromRetinaInPalletReturn(PalletReturn $palletReturn, ActionRequest $request): void
    {
        $this->initialisation($request);

        $this->handle($palletReturn, $this->validatedData);
    }

    public function action(PalletReturn|PalletDelivery $parent, array $modelData): void
    {
        $this->actionInitialisation($parent->fulfilmentCustomer, $modelData);

        $this->handle($parent, $this->validatedData);
    }
}

<?php

/*
 * Author Louis Perez
 * Created on 22-10-2026-09h-04m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Fulfilment\FulfilmentTransaction;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithFulfilmentShopEditAuthorisation;
use App\Models\Fulfilment\FulfilmentTransaction;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class BulkStoreFulfilmentTransactions extends OrgAction
{
    use WithFulfilmentShopEditAuthorisation;

    /**
     * @param  array{transactions: array<int, array{historic_asset_id: int, quantity: float}>}  $modelData
     * @return Collection<int, FulfilmentTransaction>
     */
    public function handle(PalletDelivery|PalletReturn $parent, array $modelData): Collection
    {
        return DB::transaction(function () use ($parent, $modelData) {
            return collect($modelData['transactions'])->map(
                fn (array $transaction) => StoreFulfilmentTransaction::make()->handle($parent, [
                    'historic_asset_id' => $transaction['historic_asset_id'],
                    'quantity'          => $transaction['quantity'],
                ])
            );
        });
    }

    public function rules(): array
    {
        return [
            'transactions'                     => ['required', 'array', 'min:1'],
            'transactions.*.quantity'          => ['required', 'numeric', 'gt:0'],
            'transactions.*.historic_asset_id' => [
                'required',
                'distinct',
                Rule::exists('historic_assets', 'id')
                    ->where('organisation_id', $this->organisation->id)
            ]
        ];
    }

    public function inPalletDelivery(PalletDelivery $palletDelivery, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        $this->handle($palletDelivery, $this->validatedData);
    }

    public function inPalletReturn(PalletReturn $palletReturn, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $request);

        $this->handle($palletReturn, $this->validatedData);
    }

    /**
     * @param  array{transactions: array<int, array{historic_asset_id: int, quantity: float}>}  $modelData
     * @return Collection<int, FulfilmentTransaction>
     */
    public function action(PalletDelivery|PalletReturn $parent, array $modelData): Collection
    {
        $this->asAction = true;

        $this->initialisationFromFulfilment($parent->fulfilment, $modelData);

        return $this->handle($parent, $this->validatedData);
    }
}

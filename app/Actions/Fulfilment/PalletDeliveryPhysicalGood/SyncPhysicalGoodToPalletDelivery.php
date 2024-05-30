<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDeliveryPhysicalGood;

use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydratePhysicalGoods;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletDelivery;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class SyncPhysicalGoodToPalletDelivery
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;

    public function handle(PalletDelivery $palletDelivery, array $modelData): PalletDelivery
    {
        $palletDelivery->services()->sync($modelData['product_id']);

        PalletDeliveryHydratePhysicalGoods::dispatch($palletDelivery);

        return $palletDelivery;
    }

    public function rules(): array
    {
        return [
            'product_id'            => ['required', 'array'],
            'product_id.*.quantity' => ['required', 'integer', 'min:1']
        ];
    }

    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->setRawAttributes($request->all());

        return $this->handle($palletDelivery, $this->validateAttributes());
    }
}

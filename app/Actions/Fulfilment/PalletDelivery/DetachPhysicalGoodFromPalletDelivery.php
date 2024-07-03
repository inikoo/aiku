<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Jun 2024 22:48:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydratePhysicalGoods;
use App\Actions\OrgAction;
use App\Models\Catalogue\Product;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletDelivery;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DetachPhysicalGoodFromPalletDelivery extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;

    public function handle(PalletDelivery $palletDelivery, Product $outer, array $modelData = []): PalletDelivery
    {
        $palletDelivery->physicalGoods()->detach([$outer->id]);

        PalletDeliveryHydratePhysicalGoods::dispatch($palletDelivery);

        return $palletDelivery;
    }

    public function rules(): array
    {
        return [
            'quantity'   => ['sometimes', 'integer', 'min:1']
        ];
    }

    public function asController(PalletDelivery $palletDelivery, Product $outer, ActionRequest $request): PalletDelivery
    {
        $this->initialisation($palletDelivery->organisation, $request->all());

        return $this->handle($palletDelivery, $outer, $this->validatedData);
    }

    public function fromRetina(PalletDelivery $palletDelivery, Product $outer, ActionRequest $request): PalletDelivery
    {
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        return $this->handle($palletDelivery, $outer, $this->validatedData);
    }
}

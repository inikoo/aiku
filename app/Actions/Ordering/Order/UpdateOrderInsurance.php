<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Oct 2025 14:00:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Ordering\Transaction\Traits\WithChargeTransactions;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\Ordering\WithOrderingEditAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Charge\ChargeTypeEnum;
use App\Models\Ordering\Order;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrderInsurance extends OrgAction
{
    use WithActionUpdate;
    use HasOrderHydrators;
    use WithOrderingEditAuthorisation;
    use WithChargeTransactions;


    public function handle(Order $order, array $modelData): Order
    {
        $order = $this->update($order, $modelData);
        $this->syncOrderOptionCharge($order, ChargeTypeEnum::INSURANCE, (bool) Arr::get($modelData, 'has_insurance', false));

        return $order;
    }



    public function rules(): array
    {
        return [
            'has_insurance' => ['required', 'boolean'],
        ];
    }


    public function action(Order $order, array $modelData): Order
    {
        $this->asAction = true;
        $this->initialisationFromShop($order->shop, $modelData);

        return $this->handle($order, $modelData);
    }


    public function asController(Order $order, ActionRequest $request): Order
    {
        $this->initialisationFromShop($order->shop, $request);

        return $this->handle($order, $this->validatedData);
    }
}

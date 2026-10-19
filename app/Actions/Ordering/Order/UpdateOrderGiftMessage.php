<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Helpers\Media\DetachAttachmentFromModel;
use App\Actions\Ordering\Transaction\Traits\WithChargeTransactions;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\Ordering\WithOrderingEditAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Charge\ChargeTypeEnum;
use App\Models\Ordering\Order;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrderGiftMessage extends OrgAction
{
    use WithActionUpdate;
    use HasOrderHydrators;
    use WithOrderingEditAuthorisation;
    use WithChargeTransactions;


    public function handle(Order $order, array $modelData): Order
    {
        $order = $this->update($order, $modelData);
        $this->syncOrderOptionCharge($order, ChargeTypeEnum::GIFT_MESSAGE, (bool) Arr::get($modelData, 'has_gift_message', false));

        if (!Arr::get($modelData, 'has_gift_message', false)) {
            $order->update(['gift_message' => null]);
            foreach ($order->attachments()->wherePivot('scope', 'GiftMessage')->get() as $attachment) {
                DetachAttachmentFromModel::run($order, $attachment);
            }
        }

        return $order;
    }



    public function rules(): array
    {
        return [
            'has_gift_message' => ['required', 'boolean'],
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

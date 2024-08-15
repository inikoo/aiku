<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Jun 2024 15:13:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\OrgAction;
use App\Models\Dropshipping\Platform;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class AttachOrderToPlatform extends OrgAction
{
    /**
     * @var \App\Models\Ordering\Order
     */
    private Order $order;

    public function handle(Order $order, Platform $platform, array $pivotData): Order
    {
        $pivotData['group_id']        = $this->organisation->group_id;
        $pivotData['organisation_id'] = $this->organisation->id;
        $pivotData['shop_id']         = $order->shop_id;
        $order->platforms()->attach($platform->id, $pivotData);


        return $order;
    }

    public function rules(): array
    {
        return [
            'reference' => 'nullable|string|max:255',
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if($this->order->platforms()->count() >= 1) {
            abort(403);
        }
    }

    public function action(Order $order, Platform $platform, array $modelData): Order
    {
        $this->order = $order;
        $this->initialisation($order->organisation, $modelData);

        return $this->handle($order, $platform, $this->validatedData);
    }

    public function asController(Organisation $organisation, Order $order, Platform $platform, ActionRequest $request): void
    {
        $this->initialisation($organisation, $request);
        $this->handle($order, $platform, $this->validatedData);
    }
}

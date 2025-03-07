<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 06 Mar 2025 21:56:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\OrgAction;
use App\Actions\Traits\Hydrators\WithHydrateCommand;
use App\Enums\Accounting\Payment\PaymentStatusEnum;
use App\Models\Ordering\Order;

class SetOrderPayments extends OrgAction
{
    use WithHydrateCommand;

    public string $commandSignature = 'orders:set_payments {organisations?*} {--S|shop= shop slug} {--s|slug=}';


    public function __construct()
    {
        $this->model = Order::class;
    }

    protected function handle(Order $order): Order
    {

        $paidAmount = $order->payments()->where('payments.status', PaymentStatusEnum::SUCCESS)->sum('model_has_payments.amount');

        $order->update(
            [
                'payment_amount' => $paidAmount
            ]
        );

        return $order;
    }

}

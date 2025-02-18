<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:38:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property string $quantity
 * @property string $net_amount
 * @property string $name
 * @property string $currency_code
 * @property mixed $id
 * @property mixed $in_process
 */
class InvoiceTransactionsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'code'                      => $this->code,
            'name'                      => $this->name,
            'quantity'                  => (int) $this->quantity,
            'net_amount'                => $this->net_amount,
            'currency_code'             => $this->currency_code,
            'in_process'                => $this->in_process,
            'refund_route'              => [
                'name'       => 'grp.models.invoice_transaction.refund_transaction.store',
                'parameters' => [
                    'invoiceTransaction' => $this->id,
                ]
            ],
        ];
    }
}

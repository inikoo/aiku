<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Refund;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithFixedAddressActions;
use App\Actions\Traits\WithOrderExchanges;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\Invoice;
use Illuminate\Support\Facades\DB;

class StoreRefund extends OrgAction
{
    use WithFixedAddressActions;
    use WithOrderExchanges;
    use WithNoStrictRules;




    /**
     * @throws \Throwable
     */
    public function handle(Invoice $invoice, array $modelData): Invoice
    {



        $reference = $invoice->reference . '-refund-' . rand(000, 999);

        data_set($modelData, 'reference', $reference);
        data_set($modelData, 'type', InvoiceTypeEnum::REFUND);
        data_set($modelData, 'total_amount', 0);
        data_set($modelData, 'gross_amount', 0);
        data_set($modelData, 'goods_amount', 0);
        data_set($modelData, 'net_amount', 0);
        data_set($modelData, 'grp_net_amount', 0);
        data_set($modelData, 'org_net_amount', 0);
        data_set($modelData, 'tax_amount', 0);
        data_set($modelData, 'in_process', true);
        data_set($modelData, 'invoice_id', $invoice->id);
        data_set($modelData, 'customer_id', $invoice->customer_id);
        data_set($modelData, 'currency_id', $invoice->currency_id);
        data_set($modelData, 'tax_category_id', $invoice->tax_category_id);

        $date = now();
        data_set($modelData, 'date', $date, overwrite: false);


        data_set($modelData, 'group_id', $invoice->group_id);
        data_set($modelData, 'organisation_id', $invoice->organisation_id);
        data_set($modelData, 'shop_id', $invoice->shop_id);

        return DB::transaction(function () use ($invoice, $modelData) {
            /** @var Invoice $refund */
            $refund = $invoice->refunds()->create($modelData);
            $refund->stats()->create();

            return $refund;
        });
    }

    public function rules(): array
    {
        return[

        ];



    }

    /**
     * @throws \Throwable
     */
    public function action(Invoice $invoice, array $modelData): Invoice
    {
        $this->initialisationFromShop($invoice->shop, $modelData);

        return $this->handle($invoice, $this->validatedData);
    }
}

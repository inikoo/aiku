<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\InvoiceTransactionHasFeedback;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Helpers\InvoiceTransactionHasFeedback;

class StoreInvoiceTransactionHasFeedback extends OrgAction
{
    use WithNoStrictRules;

    public function handle(InvoiceTransaction $invoiceTransaction, array $modelData): InvoiceTransactionHasFeedback
    {

        data_set($modelData, 'group_id', $invoiceTransaction->group_id);
        data_set($modelData, 'organisation_id', $invoiceTransaction->organisation_id);
        data_set($modelData, 'shop_id', $invoiceTransaction->shop_id);


        return $invoiceTransaction->feedbackBridges()->create($modelData);


    }

    public function rules(): array
    {
        $rules = [
            'post_invoice_transaction_id' => ['sometimes','nullable']// todo exist in invoices_transactions (as a post transaction)
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }


    public function action(InvoiceTransaction $invoiceTransaction, array $modelData, bool $strict = true): InvoiceTransactionHasFeedback
    {
        $this->asAction = true;
        $this->strict   = $strict;
        $this->initialisationFromShop($invoiceTransaction->shop, $modelData);

        return $this->handle($invoiceTransaction, $this->validatedData);
    }


}

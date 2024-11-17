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

class StoreOrphanInvoiceTransactionHasFeedback extends OrgAction
{
    use WithNoStrictRules;

    public function handle(InvoiceTransaction $postInvoiceTransaction, array $modelData): InvoiceTransactionHasFeedback
    {

        data_set($modelData, 'group_id', $postInvoiceTransaction->group_id);
        data_set($modelData, 'organisation_id', $postInvoiceTransaction->organisation_id);
        data_set($modelData, 'post_invoice_transaction_id', $postInvoiceTransaction->id);


        return  $postInvoiceTransaction->shop->feedbackBridges()->create($modelData);



    }

    public function rules(): array
    {
        $rules = [
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }


    public function action(InvoiceTransaction $postInvoiceTransaction, array $modelData, bool $strict = true): InvoiceTransactionHasFeedback
    {
        $this->asAction = true;
        $this->strict   = $strict;
        $this->initialisationFromShop($postInvoiceTransaction->shop, $modelData);

        return $this->handle($postInvoiceTransaction, $this->validatedData);
    }


}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 04 Sept 2024 15:22:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\InvoiceTransaction;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Accounting\InvoiceTransaction;

class UpdateInvoiceTransaction extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;


    public function handle(InvoiceTransaction $invoiceTransaction, array $modelData): InvoiceTransaction
    {
        return $this->update($invoiceTransaction, $modelData, ['data']);
    }

    public function rules(): array
    {
        $rules = [
            'quantity'            => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_ordered'    => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_bonus'      => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_dispatched' => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_fail'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_cancelled'  => ['sometimes', 'sometimes', 'numeric', 'min:0'],
            'gross_amount'        => ['sometimes', 'required', 'numeric'],
            'net_amount'          => ['sometimes', 'required', 'numeric'],
            'org_exchange'        => ['sometimes', 'numeric'],
            'grp_exchange'        => ['sometimes', 'numeric'],
            'org_net_amount'      => ['sometimes', 'numeric'],
            'grp_net_amount'      => ['sometimes', 'numeric'],
            'tax_category_id'     => ['sometimes', 'required', 'exists:tax_categories,id'],
            'date'                => ['sometimes', 'required', 'date'],
            'submitted_at'        => ['sometimes', 'required', 'date'],
        ];
        if (!$this->strict) {
            $rules = $this->noStrictUpdateRules($rules);
        }

        return $rules;
    }

    public function action(InvoiceTransaction $invoiceTransaction, array $modelData, bool $strict = true): InvoiceTransaction
    {
        $this->strict = $strict;
        $this->initialisationFromShop($invoiceTransaction->shop, $modelData);

        return $this->handle($invoiceTransaction, $this->validatedData);
    }
}

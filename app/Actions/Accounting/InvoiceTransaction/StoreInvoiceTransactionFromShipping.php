<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 04 Sept 2024 15:22:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\InvoiceTransaction;

use App\Actions\OrgAction;
use App\Actions\Traits\WithOrderExchanges;
use App\Actions\Traits\WithStoreNoProductTransaction;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;

class StoreInvoiceTransactionFromShipping extends OrgAction
{
    use WithOrderExchanges;
    use WithStoreInvoiceTransaction;
    use WithStoreNoProductTransaction;

    public function handle(Invoice $invoice, array $modelData): InvoiceTransaction
    {
        $modelData=$this->prepareShippingTransaction($modelData);

        return $this->processInvoiceTransaction($invoice, $modelData);
    }

    public function rules(): array
    {
        return $this->getRules();
    }

    public function action(Invoice $invoice, array $modelData, bool $strict = true): InvoiceTransaction
    {
        $this->asAction = true;
        $this->strict   = $strict;
        $this->initialisationFromShop($invoice->shop, $modelData);

        return $this->handle($invoice, $this->validatedData);
    }


}

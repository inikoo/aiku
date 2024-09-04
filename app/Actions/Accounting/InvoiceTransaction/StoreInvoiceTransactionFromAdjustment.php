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
use App\Models\Ordering\Adjustment;

class StoreInvoiceTransactionFromAdjustment extends OrgAction
{
    use WithOrderExchanges;
    use WithStoreNoProductTransaction;
    use WithStoreInvoiceTransaction;

    public function handle(Invoice $invoice, Adjustment $adjustment, array $modelData): InvoiceTransaction
    {
        $modelData = $this->prepareAdjustmentTransaction($adjustment, $modelData);
        $modelData = $this->processExchanges($modelData, $invoice->shop);
        return $this->processInvoiceTransaction($invoice, $modelData);
    }

    public function rules(): array
    {
        return $this->getRules();
    }

    public function action(Invoice $invoice, Adjustment $adjustment, array $modelData, bool $strict = true): InvoiceTransaction
    {
        $this->asAction = true;
        $this->strict   = $strict;
        $this->initialisationFromShop($invoice->shop, $modelData);

        return $this->handle($invoice, $adjustment, $this->validatedData);
    }


}

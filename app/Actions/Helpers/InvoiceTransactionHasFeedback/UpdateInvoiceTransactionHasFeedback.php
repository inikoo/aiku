<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 15 Nov 2024 20:23:09 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\InvoiceTransactionHasFeedback;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Helpers\InvoiceTransactionHasFeedback;

class UpdateInvoiceTransactionHasFeedback extends OrgAction
{
    use WithActionUpdate;
    use WithNoStrictRules;

    public function handle(InvoiceTransactionHasFeedback $invoiceTransactionHasFeedback, array $modelData): InvoiceTransactionHasFeedback
    {
        return $this->update($invoiceTransactionHasFeedback, $modelData);
    }

    public function rules(): array
    {
        $rules = [

        ];
        if (!$this->strict) {
            $rules = $this->noStrictUpdateRules($rules);

        }

        return $rules;
    }

    public function action(InvoiceTransactionHasFeedback $invoiceTransactionHasFeedback, array $modelData, bool $strict = true): InvoiceTransactionHasFeedback
    {
        $this->strict = $strict;
        $this->initialisationFromShop($invoiceTransactionHasFeedback->shop, $modelData);

        return $this->handle($invoiceTransactionHasFeedback, $this->validatedData);
    }
}

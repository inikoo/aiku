<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Models\Accounting\Invoice;
use Lorisleiva\Actions\Concerns\AsObject;

class GetInvoiceShowcase
{
    use AsObject;

    public function handle(Invoice $invoice): array
    {
        return [
            []
        ];
    }
}

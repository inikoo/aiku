<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 26 Mar 2023 03:35:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\TaxNumber;

use App\Models\Helpers\TaxNumber;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteTaxNumber
{
    use AsAction;

    public function handle(TaxNumber $taxNumber): bool
    {
        return $taxNumber->delete();
    }
}

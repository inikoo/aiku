<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:33 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Barcode;

use App\Models\Helpers\Barcode;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreBarcode
{
    use AsAction;

    public function handle(Barcode $barcode, $modelData): Barcode
    {
        return $barcode->create($modelData);
    }
}

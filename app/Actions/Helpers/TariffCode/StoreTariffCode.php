<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:35:06 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\TariffCode;

use App\Models\Helpers\TariffCode;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreTariffCode
{
    use AsAction;

    public function handle($modelData): TariffCode
    {
        return TariffCode::create($modelData);
    }
}

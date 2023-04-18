<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:33 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
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

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 18 Apr 2023 11:01:57 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Assets\TariffCode;

use App\Models\Assets\TariffCode;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreTariffCode
{
    use AsAction;

    public function handle($modelData): TariffCode
    {
        return TariffCode::create($modelData);
    }
}

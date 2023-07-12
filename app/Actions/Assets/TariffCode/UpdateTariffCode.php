<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 18 Apr 2023 11:01:57 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Assets\TariffCode;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Assets\TariffCode;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTariffCode
{
    use AsAction;
    use WithActionUpdate;

    public function handle(TariffCode $tariffCode, array $modelData): TariffCode
    {
        return $this->update($tariffCode, $modelData);
    }
}

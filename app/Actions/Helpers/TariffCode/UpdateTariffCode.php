<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:35:06 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\TariffCode;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Helpers\TariffCode;
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

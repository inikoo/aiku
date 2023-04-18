<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:33 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\TariffCode;

use App\Actions\WithActionUpdate;
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

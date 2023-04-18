<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 18 Apr 2023 12:43:12 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Assets\Currency;

use App\Models\Assets\Currency;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class SetCurrencyHistoricFields
{
    use AsAction;

    public function handle(Currency $currency, ?Carbon $date): void
    {
        $toUpdate =
            [
                'store_historic_data' => true
            ];

        if ($date) {
            if (!($currency->historic_data_since and $currency->historic_data_since < $date)) {
                $toUpdate['historic_data_since'] = $date->toDate();
            }
        }

        $currency->update($toUpdate);
    }
}

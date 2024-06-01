<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:35:06 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Currency\UI;

use App\Models\Helpers\Currency;
use Lorisleiva\Actions\Concerns\AsObject;

class GetCurrenciesOptions
{
    use AsObject;

    public function handle(): array
    {
        $selectOptions = [];
        /** @var Currency $currency */
        foreach (Currency::all() as $currency) {
            $selectOptions[$currency->id] =
                [
                    'label' => $currency->name.' ('.$currency->code.") $currency->symbol",
                ];
        }

        return $selectOptions;
    }
}

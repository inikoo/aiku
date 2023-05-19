<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 12:12:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Assets\Currency\UI;

use App\Models\Assets\Currency;
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

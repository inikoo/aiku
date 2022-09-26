<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 26 Sept 2022 01:22:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class AlphaDashDot implements InvokableRule
{

    public function __invoke($attribute, $value, $fail): void
    {



        if (preg_match('/^[0-9A-Za-z.\-_]+$/u', $value) == 0) {
            $fail('The :attribute may only contain letters, numbers, dashes, underscores and dots.')->translate();
        }
    }
}

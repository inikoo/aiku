<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Feb 2024 00:39:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class AlphaDashSlash implements ValidationRule
{
    public function validate($attribute, $value, $fail): void
    {
        if (preg_match('/^[\/0-9A-Za-z\-_]+$/u', $value) == 0) {
            $fail('The :attribute may only contain letters, numbers, dashes, underscores and forward slashes.')->translate();
        }
    }
}

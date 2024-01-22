<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 09:06:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class AlphaDashDotSpaceSlash implements ValidationRule
{
    public function validate($attribute, $value, $fail): void
    {
        if (preg_match('/^[\/\s0-9A-Za-z.\-_]+$/u', $value) == 0) {
            $fail('The :attribute may only contain letters, numbers, dashes, underscores, spaces, forward slashes and dots.')->translate();
        }
    }
}

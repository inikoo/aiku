<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 30 Jun 2024 11:44:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class AlphaDashDotSpaceSlashParenthesisPlus implements ValidationRule
{
    public function validate($attribute, $value, $fail): void
    {
        if (preg_match('/^[()\/\s0-9A-Za-z.\-_+]+$/u', $value) == 0) {
            $fail('The :attribute may only contain letters, numbers, parenthesis, dashes, underscores, spaces, forward slashes, plus and dots.')->translate();
        }
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 18:23:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers;

use Lorisleiva\Actions\Concerns\AsObject;

class ReadableRandomStringGenerator
{
    use AsObject;

    public function handle($length=6): string
    {
        $string     = '';
        $vowels     = array("a","e","i","o","u");
        $consonants = array(
            'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
            'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
        );

        $max = $length / 2;
        for ($i = 1; $i <= $max; $i++) {
            $string .= $consonants[rand(0, 19)];
            $string .= $vowels[rand(0, 4)];
        }

        return $string;
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 17:51:02 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Exceptions;

use Exception;

class IrisWebsiteNotFound extends Exception
{
    public static function make(): static
    {
        return new static('Domain not configured.');
    }
}

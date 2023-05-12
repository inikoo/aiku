<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 May 2023 14:08:01 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources;

trait HasSelfCall
{
    public function getArray(): array
    {
        return $this->toArray($this->resource);
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 13:16:52 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Traits;

use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;

trait UsesGroupConnection
{
    use UsesMultitenancyConfig;

    public function getConnectionName(): ?string
    {
        return 'group';
    }
}

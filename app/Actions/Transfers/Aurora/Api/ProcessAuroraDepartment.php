<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Feb 2025 17:06:59 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora\Api;

use App\Actions\OrgAction;
use App\Actions\Transfers\Aurora\FetchAuroraDepartments;

/**
 * @property string $fetcher
 */
class ProcessAuroraDepartment extends OrgAction
{
    use WithProcessAurora;

    public function __construct()
    {
        $this->fetcher = FetchAuroraDepartments::class;
    }

}

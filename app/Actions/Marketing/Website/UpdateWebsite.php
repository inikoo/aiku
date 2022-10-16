<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 18:04:15 Central European Summer Time, Benalmádena, Malaga Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Website;

use App\Actions\WithActionUpdate;
use App\Models\Marketing\Website;


class UpdateWebsite
{
    use WithActionUpdate;

    public function handle(Website $website, array $modelData): Website
    {
        return $this->update($website, $modelData, ['data', 'settings']);
    }
}

<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:30:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\WithActionUpdate;
use App\Models\Web\Website;


class UpdateWebsite
{
    use WithActionUpdate;

    public function handle(Website $website, array $modelData): Website
    {
        return $this->update($website, $modelData, ['data', 'settings']);
    }
}

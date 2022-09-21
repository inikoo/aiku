<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 16:05:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Helpers;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class TenantAwareMediaUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        $url = asset($this->getPathRelativeToRoot());

        return $this->versionUrl($url);
    }
}

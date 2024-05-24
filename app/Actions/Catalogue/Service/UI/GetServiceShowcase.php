<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

 namespace App\Actions\Catalogue\Service\UI;

use App\Models\Catalogue\Product;
use App\Models\Catalogue\Service;
use Lorisleiva\Actions\Concerns\AsObject;

class GetServiceShowcase
{
    use AsObject;

    public function handle(Service $service): array
    {
        return [
            []
        ];
    }
}
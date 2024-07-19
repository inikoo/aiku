<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Jul 2024 15:58:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Http\Resources\Catalogue\FamilyWebsiteResource;
use App\Models\Catalogue\ProductCategory;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWebsiteWorkshopFamily
{
    use AsObject;

    public function handle(Website $website, ProductCategory $category): array
    {
        return [
            'category' => FamilyWebsiteResource::make($category)

        ];
    }
}

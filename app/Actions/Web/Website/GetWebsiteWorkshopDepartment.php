<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Jul 2024 16:11:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Http\Resources\Catalogue\DepartmentWebsiteResource;
use App\Models\Catalogue\ProductCategory;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWebsiteWorkshopDepartment
{
    use AsObject;

    public function handle(Website $website, ProductCategory $category): array
    {
        return [
            'category' => DepartmentWebsiteResource::make($category)

        ];
    }
}

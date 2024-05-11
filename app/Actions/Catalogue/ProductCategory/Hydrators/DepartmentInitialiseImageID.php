<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:34:41 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\Hydrators;

use App\Models\Catalogue\ProductCategory;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Fill image_id if id null and products has images (to be run after trade units set up or first image added)
 */
class DepartmentInitialiseImageID implements ShouldBeUnique
{
    use AsAction;


    public function handle(ProductCategory $department): void
    {
        if ($department->images()->count()) {
            if ($department->image_id) {
                return;
            }

            $image = $department->images()->first();

            if ($image) {
                $department->update(
                    [
                        'image_id' => $image->id
                    ]
                );
            }
        }
    }

    public function getJobUniqueId(ProductCategory $department): string
    {
        return $department->id;
    }
}

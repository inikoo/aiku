<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 11:37:01 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Department\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Marketing\Department;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Fill image_id if id null and products has images (to be run after trade units set up or first image added)
 */
class DepartmentInitialiseImageID implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Department $department): void
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

    public function getJobUniqueId(Department $department): string
    {
        return $department->id;
    }
}

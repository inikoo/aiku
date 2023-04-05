<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 04 Apr 2023 11:42:49 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Family\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Marketing\Family;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Fill image_id if id null and products has images (to be run after trade units set up or first image added)
 */
class FamilyInitialiseImageID implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Family $family): void
    {
        $image_id = null;
        if ($family->images()->count()) {
            if ($family->image_id) {
                return;
            }

            $image = $family->images()->first();

            if ($image) {
                $family->update(
                    [
                        'image_id' => $image->id
                    ]
                );
            }
        }
    }

    public function getJobUniqueId(Family $family): string
    {
        return $family->id;
    }
}

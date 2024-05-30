<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 30 May 2024 08:37:52 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Media\Media;

use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\HumanResources\Employee;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveModelImage
{
    use AsAction;

    public function handle(
        User|WebUser|Agent|Supplier|Employee|Guest|Customer|Group|Organisation|Shop $model,
        array $imageData,
        string $scope = 'image'
    ): User|WebUser|Agent|Supplier|Employee|Guest|Customer|Group|Organisation|Shop {
        $oldImage = $model->image;
        $media    = StoreMediaFromFile::run($model, $imageData);

        $group_id        = $model->group_id;
        $organisation_id = $model->organisation_id;
        if ($model instanceof Group) {
            $group_id = $model->id;
        }
        if ($model instanceof Organisation) {
            $organisation_id = $model->id;
        }


        if ($media) {
            $model->updateQuietly(['image_id' => $media->id]);

            $model->images()->sync(
                [
                    $media->id => [
                        'group_id'        => $group_id,
                        'organisation_id' => $organisation_id,
                        'scope'           => $scope,
                    ]
                ]
            );
            if($oldImage) {
                $oldImage->delete();
            }

        }

        return $model;
    }

}

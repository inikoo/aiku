<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 30 May 2024 08:37:52 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Models\Catalogue\Shop;
use App\Models\CRM\WebUser;
use App\Models\Media\Media;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;

trait WithAttachMediaToModel
{
    protected function attachMediaToModel(Group|Organisation|Shop|User|Webuser $model, Media $media, $scope = 'default'): Group|Organisation|Shop|User|Webuser
    {
        $model->updateQuietly(
            [
                'image_id' => $media->id
            ]
        );

        $group_id = $model->group_id;

        if ($model instanceof Group || $model instanceof User) {
            $organisation_id = null;
            if ($model instanceof Group) {
                $group_id = $model->id;
            }
        } elseif ($model instanceof Organisation) {
            $organisation_id = $model->id;
        } else {
            $organisation_id = $model->organisation_id;
        }


        $model->images()->attach(
            [
                $media->id => [
                    'group_id'        => $group_id,
                    'organisation_id' => $organisation_id,
                    'scope'           => $scope
                ]
            ]
        );

        return $model;
    }

}
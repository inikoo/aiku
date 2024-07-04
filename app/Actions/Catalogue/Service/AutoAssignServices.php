<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Apr 2024 12:42:18 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Service;

use App\Actions\Fulfilment\PalletDelivery\DetachServiceFromPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\SyncServiceToPalletDelivery;
use App\Actions\Fulfilment\PalletReturn\DetachServiceFromPalletReturn;
use App\Actions\Fulfilment\PalletReturn\SyncServiceToPalletReturn;
use App\Actions\OrgAction;
use App\Models\Catalogue\Service;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;

class AutoAssignServices extends OrgAction
{
    public function handle (PalletDelivery|PalletReturn $parent, $subject)
    {
        $service = Service::where([
            ['is_auto_assign', true],
            ['auto_assign_trigger', class_basename($parent)],
            ['auto_assign_subject', class_basename($subject)],
            ['auto_assign_subject_type', $subject->type]
        ])->first();

        $quantity = $parent->pallets()->where('type', $subject->type)->count();

        data_set($modelData, 'service_id', $service->id);
        data_set($modelData, 'quantity', $quantity);

       if($parent instanceof PalletDelivery)
       {
            SyncServiceToPalletDelivery::run($parent, $modelData);
            if($quantity == 0)
            {
                DetachServiceFromPalletDelivery::run($parent, $service);
            }
       } else {
            SyncServiceToPalletReturn::run($parent, $modelData);
            if($quantity == 0)
            {
                DetachServiceFromPalletReturn::run($parent, $service);
            }
       }

       return $parent;
    }
}
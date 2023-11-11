<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Firebase;

use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsObject;

class CheckUserStatusFirebase
{
    use AsObject;
    use AsAction;

    public function handle($organisation): void
    {
        $database  = app('firebase.database');
        $reference = $database->getReference($organisation->slug);

        $values = $reference->getValue();

        foreach ($values as $value) {
            if(Carbon::make($value['last_active'])->timestamp < now()->subMinutes(5)->timestamp && $value['is_active']) {
                $database->getReference($organisation->slug . '/' . $value['username'] . '/is_active')->set(false);
            }

            if(Carbon::make($value['last_active'])->timestamp < now()->subMinutes(120)->timestamp) {
                $reference->removeChildren([$value['username']]);
            }
        }
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Firebase;

use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsObject;

class StoreUserLogFirebase
{
    use AsObject;
    use AsAction;

    public function handle(string $username, int $groupId, array $route): void
    {
        $database = app('firebase.database');
        $path     = 'org/'.$groupId.'/active_users/'.$username;

        $reference = $database->getReference($path);

        $reference->set([
            'route'       => $route,
            'last_active' => now()
        ]);
        //CheckUserStatusFirebase::dispatch($organisation);
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Firebase;

use App\Models\Auth\User;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsObject;

class StoreUserLogFirebase
{
    use AsObject;
    use AsAction;

    public function handle(User $user): void
    {
        $tenant = app('currentTenant');
        $database = app('firebase.database');
        $reference = $database->getReference($tenant->slug . '/' . $user->username);

        $reference->set([
            'is_active' => true,
            'username' => $user->username,
            'contact_name' => $user->contact_name,
            'route' => [
                'module' => explode('.', request()->route()->getName())[0],
                'name' => request()->route()->getName(),
                'arguments' => request()->route()->originalParameters()
            ],
            'timestamp' => now()
        ]);

        CheckUserStatusFirebase::dispatch($tenant);
    }
}

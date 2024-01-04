<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 21:50:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\Hydrators;

use App\Models\SysAdmin\User;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class UserHydrateAuthorisedModels
{
    use AsAction;

    public function handle(User $user): void
    {
        setPermissionsTeamId($user->group->id);

        $authorisedOrganisations = [];
        $authorisedShops         = [];
        $authorisedWarehouses    =[];

        foreach ($user->getAllPermissions() as $permission) {
            if ($permission->scope_type === 'Organisation') {
                $authorisedOrganisations[$permission->scope_id] = $permission->scope_id;
            } elseif ($permission->scope_type === 'Shop') {
                $authorisedShops[$permission->scope_id] = $permission->scope_id;
            } elseif ($permission->scope_type === 'Warehouse') {
                $authorisedWarehouses[$permission->scope_id] = $permission->scope_id;
            }
        }

        $user->authorisedOrganisations()->sync($authorisedOrganisations);
        $user->authorisedShops()->sync($authorisedShops);
        $user->authorisedWarehouses()->sync($authorisedWarehouses);

        $stats = [
            'number_authorised_organisations' => count($authorisedOrganisations),
            'number_authorised_shops'         => count($authorisedShops),
            'number_authorised_warehouses'    => count($authorisedWarehouses),
        ];

        $user->update($stats);
    }

    public string $commandSignature = 'user:hydrate-authorised-models {user : User slug}';


    public function asCommand(Command $command): int
    {
        try {
            $user = User::where('slug', $command->argument('user'))->firstOrFail();
        } catch (Exception) {
            $command->error("User {$command->argument('user')} not found");

            return 1;
        }

        $this->handle($user);

        $command->info("User authorised models hydrated 💦");

        return 0;
    }


}

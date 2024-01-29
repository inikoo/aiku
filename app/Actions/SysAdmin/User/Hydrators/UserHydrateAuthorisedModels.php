<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 21:50:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\Hydrators;

use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Warehouse;
use App\Models\Market\Shop;
use App\Models\SysAdmin\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class UserHydrateAuthorisedModels
{
    use AsAction;
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->user->id))];
    }



    public function handle(User $user): void
    {
        setPermissionsTeamId($user->group->id);

        $authorisedOrganisations = [];
        $authorisedShops         = [];
        $authorisedFulfilments   = [];
        $authorisedWarehouses    = [];

        foreach ($user->getAllPermissions() as $permission) {
            if ($permission->scope_type === 'Organisation') {
                $authorisedOrganisations[$permission->scope_id] = ['org_id' => $permission->scope_id];
            } elseif ($permission->scope_type === 'Shop') {
                $shop                                   = Shop::find($permission->scope_id);
                $authorisedShops[$permission->scope_id] = ['org_id' => $shop->organisation_id];
            } elseif ($permission->scope_type === 'Fulfilment') {
                $fulfilment                                   = Fulfilment::find($permission->scope_id);
                $authorisedFulfilments[$permission->scope_id] = ['org_id' => $fulfilment->organisation_id];
            } elseif ($permission->scope_type === 'Warehouse') {
                $warehouse                                   = Warehouse::find($permission->scope_id);
                $authorisedWarehouses[$permission->scope_id] = ['org_id' => $warehouse->organisation_id];
            }
        }


        $user->authorisedOrganisations()->sync($authorisedOrganisations);
        $user->authorisedShops()->sync($authorisedShops);
        $user->authorisedFulfilments()->sync($authorisedFulfilments);
        $user->authorisedWarehouses()->sync($authorisedWarehouses);

        $stats = [
            'number_authorised_organisations' => count($authorisedOrganisations),
            'number_authorised_shops'         => count($authorisedShops),
            'number_authorised_fulfilments'   => count($authorisedFulfilments),
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

        $command->info("User $user->contact_name authorised models hydrated 💦");

        return 0;
    }


}

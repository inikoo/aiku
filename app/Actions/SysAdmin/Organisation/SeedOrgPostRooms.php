<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 30 Nov 2024 00:35:01 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\Comms\OrgPostRoom\StoreOrgPostRoom;
use App\Actions\Comms\OrgPostRoom\UpdateOrgPostRoom;
use App\Actions\OrgAction;
use App\Models\SysAdmin\Organisation;
use Exception;
use Throwable;

class SeedOrgPostRooms extends OrgAction
{
    use WithOrganisationCommand;

    public function handle(Organisation $organisation): void
    {
        foreach ($organisation->group->postRooms as $postRoom) {
            $orgPostRoom = $organisation->orgPostRooms()->where('type', $postRoom->code->value)->first();


            if ($orgPostRoom) {
                UpdateOrgPostRoom::make()->action(
                    $orgPostRoom,
                    [
                        'name' => $postRoom->name
                    ]
                );
            } else {
                try {
                    StoreOrgPostRoom::make()->action(
                        $postRoom,
                        $organisation,
                        []
                    );
                } catch (Exception|Throwable $e) {
                    echo $e->getMessage()."\n";
                }
            }
        }
    }

    public string $commandSignature = 'org:seed_org_post_rooms {organisation? : The organisation slug}';



}

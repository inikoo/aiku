<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jul 2024 13:59:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\Comms\Outbox\StoreOutbox;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedOrganisationOutboxes
{
    use AsAction;
    use WithOrganisationCommand;

    public function handle(Organisation $organisation): void
    {
        foreach (OutboxCodeEnum::cases() as $case) {
            if ($case->scope() == 'Organisation') {
                $postRoom = $organisation->group->postRooms()->where('code', $case->postRoomCode())->first();

                if (!$organisation->outboxes()->where('code', $case)->exists()) {
                    StoreOutbox::make()->action(
                        $postRoom,
                        $organisation,
                        [
                            'name' => $case->label(),
                            'code' => $case,
                            'type' => $case->type(),
                            'state' => $case->defaultState(),

                        ]
                    );
                }
            }
        }
    }

    public string $commandSignature = 'org:seed-outboxes {organisation? : The organisation slug}';




}

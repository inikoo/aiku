<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Nov 2023 22:26:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Outbox;

use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Mail\PostRoom;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedOrganisationOutboxes
{
    use AsAction;

    public function handle(Organisation $organisation): void
    {
        foreach (OutboxTypeEnum::cases() as $case) {
            if ($case->scope() == 'organisation') {
                $postRoom = PostRoom::where('code', $case->postRoomCode()->value)->first();

                if (!Outbox::where('type', $case)->exists()) {
                    StoreOutbox::run(
                        $postRoom,
                        $organisation,
                        [
                            'name'  => $case->label(),
                            'type'  => $case,
                            'state' => $case->defaultState()

                        ]
                    );
                }
            }
        }
    }

    public string $commandSignature = 'org:seed-outboxes {organisation : The organisation slug}';

    public function asCommand(Command $command): int
    {
        try {
            $organisation = Organisation::where('slug', $command->argument('organisation'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());
            return 1;
        }

        $this->handle($organisation);

        return 0;
    }


}

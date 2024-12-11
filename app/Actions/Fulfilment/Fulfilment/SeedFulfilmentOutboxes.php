<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jul 2024 11:19:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment;

use App\Actions\Comms\Outbox\StoreOutbox;
use App\Actions\Comms\Outbox\UpdateOutbox;
use App\Actions\Traits\WithOutboxBuilder;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\Comms\Outbox;
use App\Models\Comms\PostRoom;
use App\Models\Fulfilment\Fulfilment;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedFulfilmentOutboxes
{
    use AsAction;
    use WithOutboxBuilder;

    public function handle(Fulfilment $fulfilment): void
    {
        foreach (OutboxCodeEnum::cases() as $case) {
            if (in_array('Fulfilment', $case->scope())) {
                $postRoom    = PostRoom::where('code', $case->postRoomCode()->value)->first();
                $orgPostRoom = $postRoom->orgPostRooms()->where('organisation_id', $fulfilment->organisation->id)->first();


                if ($outbox = Outbox::where('fulfilment_id', $fulfilment->id)->where('code', $case)->first()) {
                    UpdateOutbox::make()->action(
                        $outbox,
                        [
                            'name' => $case->label(),
                        ]
                    );
                } else {
                    $outbox = StoreOutbox::make()->action(
                        $orgPostRoom,
                        $fulfilment,
                        [
                            'name'       => $case->label(),
                            'code'       => $case,
                            'type'       => $case->type(),
                            'state'      => $case->defaultState(),
                            'model_type' => $case->modelType(),
                            'builder'    => $this->getDefaultBuilder($case, $fulfilment)
                        ]
                    );
                }
                $this->setEmailOngoingRuns($outbox, $case, $fulfilment);
            }
        }
    }

    public string $commandSignature = 'fulfilment:seed_outboxes {fulfilment? : The fulfilment slug}';

    public function asCommand(Command $command): int
    {
        if ($command->argument('fulfilment') == null) {
            $fulfilments = Fulfilment::all();
            foreach ($fulfilments as $fulfilment) {
                $this->handle($fulfilment);
            }

            return 0;
        }

        try {
            $fulfilment = Fulfilment::where('slug', $command->argument('fulfilment'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $this->handle($fulfilment);

        return 0;
    }


}

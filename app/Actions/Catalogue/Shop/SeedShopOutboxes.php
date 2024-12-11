<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jul 2024 13:58:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop;

use App\Actions\Comms\Outbox\StoreOutbox;
use App\Actions\Comms\Outbox\UpdateOutbox;
use App\Actions\Traits\WithOutboxBuilder;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Outbox;
use App\Models\Comms\PostRoom;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedShopOutboxes
{
    use AsAction;
    use WithOutboxBuilder;

    public function handle(Shop $shop): void
    {
        foreach (OutboxCodeEnum::cases() as $case) {
            if (in_array('Shop', $case->scope()) and in_array($shop->type->value, $case->shopTypes())) {
                $postRoom    = PostRoom::where('code', $case->postRoomCode()->value)->first();
                $orgPostRoom = $postRoom->orgPostRooms()->where('organisation_id', $shop->organisation->id)->first();

                /** @var Outbox $outbox */
                if ($outbox = Outbox::where('shop_id', $shop->id)->where('code', $case)->first()) {
                    UpdateOutbox::make()->action(
                        $outbox,
                        [
                            'name' => $case->label(),
                        ]
                    );
                } else {
                    $outbox = StoreOutbox::make()->action(
                        $orgPostRoom,
                        $shop,
                        [
                            'name'       => $case->label(),
                            'code'       => $case,
                            'type'       => $case->type(),
                            'state'      => $case->defaultState(),
                            'model_type' => $case->modelType(),
                            'builder'    => $this->getDefaultBuilder($case, $shop)

                        ]
                    );
                }

                $this->setEmailOngoingRuns($outbox, $case, $shop);
            }
        }
    }

    public string $commandSignature = 'shop:seed_outboxes {shop? : The shop slug}';

    public function asCommand(Command $command): int
    {
        if ($command->argument('shop') == null) {
            $shops = Shop::all();
            foreach ($shops as $shop) {
                $this->handle($shop);
            }

            return 0;
        }
        try {
            $shop = Shop::where('slug', $command->argument('shop'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $this->handle($shop);

        return 0;
    }


}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 01 Jul 2024 15:15:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Mail\Outbox\StoreOutbox;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Mail\Outbox;
use App\Models\Mail\PostRoom;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedShopOutboxes
{
    use AsAction;

    public function handle(Shop $shop): void
    {
        foreach (OutboxTypeEnum::cases() as $case) {
            if ($case->scope() == 'shop') {
                $postRoom = PostRoom::where('code', $case->postRoomCode()->value)->first();


                if (!Outbox::where('shop_id', $shop->id)->where('type', $case)->exists()) {
                    StoreOutbox::run(
                        $postRoom,
                        $shop,
                        [
                            'name'    => $case->label(),
                            'type'    => $case,
                            'state'   => $case->defaultState()

                        ]
                    );
                }
            }
        }
    }

    public string $commandSignature = 'shop:seed-outboxes {shop : The shop slug}';

    public function asCommand(Command $command): int
    {
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

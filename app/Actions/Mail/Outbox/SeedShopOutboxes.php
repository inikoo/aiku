<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Nov 2023 19:27:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Outbox;

use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Mail\Mailroom;
use App\Models\Mail\Outbox;
use App\Models\Catalogue\Shop;
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
                $mailroom = Mailroom::where('code', $case->mailroomCode()->value)->first();


                $outboxType = str($case->value)->camel()->kebab()->value();
                if (!Outbox::where('shop_id', $shop->id)->where('type', $outboxType)->exists()) {
                    StoreOutbox::run(
                        $mailroom,
                        [
                            'shop_id' => $shop->id,
                            'name'    => $case->label(),
                            'type'    => $outboxType,
                            'state'   => $case->defaultState()

                        ]
                    );
                }
            }
        }
    }

    public string $commandSignature = 'shop:seed-outboxes {shop}';

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

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jul 2024 13:58:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop;

use App\Actions\Comms\Email\StoreEmail;
use App\Actions\Comms\EmailOngoingRun\StoreEmailOngoingRun;
use App\Actions\Comms\Outbox\StoreOutbox;
use App\Actions\Comms\Outbox\UpdateOutbox;
use App\Enums\Comms\EmailTemplate\EmailTemplateStateEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Enums\Comms\Outbox\OutboxTypeEnum;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\EmailTemplate;
use App\Models\Comms\Outbox;
use App\Models\Comms\PostRoom;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class SeedShopOutboxes
{
    use AsAction;

    public function handle(Shop $shop): void
    {
        foreach (OutboxCodeEnum::cases() as $case) {
            if ($case->scope() == 'Shop' and in_array($shop->type->value, $case->shopTypes())) {
                $postRoom = PostRoom::where('code', $case->postRoomCode()->value)->first();
                if ($outbox = Outbox::where('shop_id', $shop->id)->where('code', $case)->exists()) {
                    $outbox = UpdateOutbox::run($outbox, [
                        'name' => $case->label(),
                        'code' => $case,
                        'type' => $case->type(),
                        'state' => $case->defaultState()
                    ]);

                    if ($outbox->type == OutboxTypeEnum::APP_COMMS) {
                        StoreEmailOngoingRun::make()->action($outbox, [
                            'subject' => $case->label(),
                        ]);
                    }
                } else {
                    $outbox = StoreOutbox::make()->action(
                        $postRoom,
                        $shop,
                        [
                            'name' => $case->label(),
                            'code' => $case,
                            'type' => $case->type(),
                            'state' => $case->defaultState(),

                        ]
                    );
                    if ($outbox->type == OutboxTypeEnum::APP_COMMS or $outbox->type == OutboxTypeEnum::TRANSACTIONAL) {
                        // try {
                        $emailOngoingRun = StoreEmailOngoingRun::make()->action($outbox, [
                            'subject' => $case->label(),
                        ]);

                        $emailTemplate = EmailTemplate::where('state', EmailTemplateStateEnum::ACTIVE)
                            ->whereJsonContains('data->outboxes', $outbox->code)->first();

                        if ($emailTemplate) {
                            $email = StoreEmail::make()->action(
                                $emailOngoingRun,
                                $emailTemplate,
                                modelData: [
                                    'snapshot_state' => SnapshotStateEnum::LIVE,
                                    'snapshot_published_at' => $shop->created_at,
                                    'snapshot_recyclable' => false,
                                    'snapshot_first_commit' => true
                                ],
                                strict: false
                            );
                            $emailOngoingRun->updateQuietly(
                                [
                                    'email_id' => $email->id
                                ]
                            );
                        }
                        //                        } catch (Exception|Throwable) {
                        //                        }
                    }
                }
            }
        }
    }

    public string $commandSignature = 'shop:seed-outboxes {shop? : The shop slug}';

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

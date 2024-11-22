<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateOutboxes;
use App\Actions\Comms\EmailTemplate\StoreEmailTemplate;
use App\Actions\Comms\PostRoom\Hydrators\PostRoomHydrateOutboxes;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateOutboxes;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOutboxes;
use App\Enums\Comms\Outbox\OutboxBlueprintEnum;
use App\Enums\Comms\Outbox\OutboxStateEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Outbox;
use App\Models\Comms\PostRoom;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Arr;
use Illuminate\Validation\Rule;

class StoreOutbox extends OrgAction
{
    public function handle(PostRoom $postRoom, Organisation|Shop|Website|Fulfilment $parent, array $modelData): Outbox
    {
        // $layout = Arr::get($modelData, 'layout', []);
        // data_forget($modelData, 'layout');

        data_set($modelData, 'group_id', $parent->group_id);

        if ($parent instanceof Shop) {
            data_set($modelData, 'organisation_id', $parent->organisation_id);
            data_set($modelData, 'shop_id', $parent->id);
        } elseif ($parent instanceof Website) {
            data_set($modelData, 'organisation_id', $parent->organisation_id);
            data_set($modelData, 'shop_id', $parent->shop_id);
            data_set($modelData, 'website_id', $parent->id);
        } elseif ($parent instanceof Fulfilment) {
            data_set($modelData, 'organisation_id', $parent->organisation_id);
            data_set($modelData, 'shop_id', $parent->shop_id);
            data_set($modelData, 'fulfilment_id', $parent->id);
        } else {
            data_set($modelData, 'organisation_id', $parent->id);
        }

        if (Arr::get($modelData, 'blueprint') == OutboxBlueprintEnum::EMAIL_TEMPLATE) {
            data_set($modelData, 'state', OutboxStateEnum::IN_PROCESS);
        } else {
            data_set($modelData, 'state', OutboxStateEnum::ACTIVE);
        }

        /** @var Outbox $outbox */
        $outbox = $postRoom->outboxes()->create($modelData);
        $outbox->stats()->create();

        //if ($outbox->blueprint == OutboxBlueprintEnum::EMAIL_TEMPLATE) {
        //    StoreEmailTemplate::make()->action($outbox, [
        //        'layout' => $layout
        //    ]);
        //}

        GroupHydrateOutboxes::run($outbox->group);
        OrganisationHydrateOutboxes::run($outbox->organisation);
        if ($outbox->shop_id) {
            ShopHydrateOutboxes::run($outbox->shop);
        }
        PostRoomHydrateOutboxes::run($outbox->postRoom);

        return $outbox;
    }


    public function rules(): array
    {
        return [
            'type'      => ['required', Rule::enum(OutboxCodeEnum::class)],
            'name'      => ['required', 'max:250', 'string'],
            'blueprint' => ['required', Rule::enum(OutboxBlueprintEnum::class)],
        ];
    }

    public function action(PostRoom $postRoom, Organisation|Shop|Website|Fulfilment $parent, array $modelData): Outbox
    {
        $this->asAction = true;
        if ($parent instanceof Organisation) {
            $organisation = $parent;
        } else {
            $organisation = $parent->organisation;
        }
        $this->initialisation($organisation, $modelData);

        return $this->handle($postRoom, $parent, $this->validatedData);
    }
}

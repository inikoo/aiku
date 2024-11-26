<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateOutboxes;
use App\Actions\Comms\PostRoom\Hydrators\PostRoomHydrateOutboxes;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateOutboxes;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOutboxes;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Enums\Comms\Outbox\OutboxStateEnum;
use App\Enums\Comms\Outbox\OutboxTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Outbox;
use App\Models\Comms\PostRoom;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Illuminate\Validation\Rule;

class StoreOutbox extends OrgAction
{
    use WithNoStrictRules;

    public function handle(PostRoom $postRoom, Organisation|Shop|Website|Fulfilment $parent, array $modelData): Outbox
    {


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


        /** @var Outbox $outbox */
        $outbox = $postRoom->outboxes()->create($modelData);
        $outbox->stats()->create();



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
        $rules = [
            'code'      => ['required', Rule::enum(OutboxCodeEnum::class)],
            'type'      => ['required', Rule::enum(OutboxTypeEnum::class)],
            'state'     => ['required', Rule::enum(OutboxStateEnum::class)],
            'name'      => ['required', 'max:250', 'string'],
        ];
        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);

        }

        return $rules;
    }

    public function action(PostRoom $postRoom, Organisation|Shop|Website|Fulfilment $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true): Outbox
    {
        $this->asAction = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        if ($parent instanceof Organisation) {
            $organisation = $parent;
        } else {
            $organisation = $parent->organisation;
        }
        $this->initialisation($organisation, $modelData);

        return $this->handle($postRoom, $parent, $this->validatedData);
    }
}

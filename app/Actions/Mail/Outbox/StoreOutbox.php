<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Outbox;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateOutboxes;
use App\Actions\Mail\EmailTemplate\StoreEmailTemplate;
use App\Actions\Mail\PostRoom\Hydrators\PostRoomHydrateOutboxes;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateOutboxes;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOutboxes;
use App\Enums\Mail\Outbox\OutboxBlueprintEnum;
use App\Enums\Mail\Outbox\OutboxStateEnum;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Mail\PostRoom;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Arr;
use Illuminate\Validation\Rule;

class StoreOutbox extends OrgAction
{
    public function handle(PostRoom $postRoom, Organisation|Shop|Website|Fulfilment $parent, array $modelData): Outbox
    {
        $layout = Arr::get($modelData, 'layout', []);
        data_forget($modelData, 'layout');

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

        if ($outbox->blueprint == OutboxBlueprintEnum::EMAIL_TEMPLATE) {
            StoreEmailTemplate::make()->action($outbox, [
                'layout' => $layout
            ]);
        }

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
            'type'      => ['required', Rule::enum(OutboxTypeEnum::class)],
            'name'      => ['required', 'max:250', 'string'],
            'blueprint' => ['required', Rule::enum(OutboxBlueprintEnum::class)],
            'layout'    => ['sometimes', 'array']
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

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Outbox;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateOutboxes;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateOutboxes;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOutboxes;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Mail\PostRoom;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreOutbox extends OrgAction
{
    use AsAction;
    use WithAttributes;


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

        return $outbox;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("mail.edit");
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(OutboxTypeEnum::class)],
            'name' => ['required', 'max:250', 'string'],
        ];
    }

    public function action(PostRoom $postRoom, Organisation|Shop $parent, array $modelData): Outbox
    {
        $this->asAction = true;
        if ($parent instanceof Shop) {
            $organisation = $parent->organisation;
        } else {
            $organisation = $parent;
        }
        $this->initialisation($organisation, $modelData);

        return $this->handle($postRoom, $parent, $this->validatedData);
    }
}

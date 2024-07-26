<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAudit;

use App\Actions\OrgAction;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemAudit;
use App\Rules\AlphaDashDotSpaceSlashParenthesisPlus;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreStoredItemAudit extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public FulfilmentCustomer $fulfilmentCustomer;

    public function handle(FulfilmentCustomer $parent, array $modelData): StoredItemAudit
    {
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'fulfilment_id', $parent->fulfilment_id);

        /** @var StoredItemAudit $storedItemAuditAudit */
        $storedItemAuditAudit = $parent->storedItemAudits()->create($modelData);

        // Hydrators

        return $storedItemAuditAudit;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($request->user() instanceof WebUser) {
            return true;
        }

        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }


    public function rules(): array
    {
        return [
            'reference'    => ['required', 'max:128',  new AlphaDashDotSpaceSlashParenthesisPlus(),
             new IUnique(
                 table: 'stored_item_audits',
                 extraConditions: [
                     ['column' => 'fulfilment_customer_id', 'value' => $this->fulfilmentCustomer->id],
                 ]
             )
            ]
        ];
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): StoredItemAudit
    {
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->fulfilment         = $fulfilmentCustomer->fulfilment;

        $this->initialisation($fulfilmentCustomer->organisation, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, array $modelData): StoredItemAudit
    {
        $this->asAction           = true;
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->fulfilment         = $fulfilmentCustomer->fulfilment;

        $this->initialisation($fulfilmentCustomer->organisation, $modelData);

        return $this->handle($fulfilmentCustomer, $this->validateAttributes());
    }

    public function htmlResponse(StoredItemAudit $storedItemAudit, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.org.fulfilments.show.crm.customers.show.stored-item-audits.show', [
            'organisation'       => $storedItemAudit->organisation->slug,
            'fulfilment'         => $storedItemAudit->fulfilment->slug,
            'fulfilmentCustomer' => $storedItemAudit->fulfilmentCustomer->slug,
            'storedItemAudit'    => $storedItemAudit->slug
        ]);
    }
}

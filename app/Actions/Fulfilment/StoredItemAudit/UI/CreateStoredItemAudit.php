<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAudit\UI;

use App\Actions\Fulfilment\StoredItemAudit\StoreStoredItemAudit;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\StoredItemAudit\StoredItemAuditStateEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class CreateStoredItemAudit extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;



    private FulfilmentCustomer $parent;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): StoredItemAudit
    {
        $storedItemAudit = $fulfilmentCustomer->storedItemAudits()->where('state', StoredItemAuditStateEnum::IN_PROCESS)->first();


        if (!$storedItemAudit) {
            $storedItemAudit = StoreStoredItemAudit::make()->action($fulfilmentCustomer, $modelData);
        }


        return $storedItemAudit;
    }

    public function htmlResponse(StoredItemAudit $storedItemAudit, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.org.fulfilments.show.crm.customers.show.stored-item-audits.show', [
            $storedItemAudit->organisation->slug,
            $storedItemAudit->fulfilment->slug,
            $storedItemAudit->fulfilmentCustomer->slug,
            $storedItemAudit->slug
        ]);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): StoredItemAudit
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


}

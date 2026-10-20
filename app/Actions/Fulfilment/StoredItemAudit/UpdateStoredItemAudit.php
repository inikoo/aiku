<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAudit;

use App\Actions\Traits\Authorisations\WithStoredItemAuditAuthorisation;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Fulfilment\StoredItemAuditsResource;
use App\Models\Fulfilment\StoredItemAudit;
use Lorisleiva\Actions\ActionRequest;

class UpdateStoredItemAudit extends OrgAction
{
    use WithStoredItemAuditAuthorisation;
    use WithActionUpdate;


    public function handle(StoredItemAudit $storedItemAudit, array $modelData): StoredItemAudit
    {
        return $this->update($storedItemAudit, $modelData, ['data']);
    }



    public function rules(): array
    {
        return [
            'public_notes'   => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],

        ];
    }

    public function asController(StoredItemAudit $storedItemAudit, ActionRequest $request): StoredItemAudit
    {
        $this->warehouse = $storedItemAudit->warehouse;
        $this->initialisationFromFulfilment($storedItemAudit->fulfilment, $request);

        return $this->handle($storedItemAudit, $this->validatedData);
    }

    public function action(StoredItemAudit $storedItemAudit, array $modelData): StoredItemAudit
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($storedItemAudit->fulfilment, $modelData);

        return $this->handle($storedItemAudit, $this->validatedData);
    }

    public function jsonResponse(StoredItemAudit $storedItem): StoredItemAuditsResource
    {
        return new StoredItemAuditsResource($storedItem);
    }
}

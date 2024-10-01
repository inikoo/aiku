<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 01 Oct 2024 10:17:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\CustomerNote;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Catalogue\Shop;
use App\Models\CRM\CustomerNote;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class UpdateCustomerNote extends OrgAction
{
    use WithActionUpdate;

    public function handle(CustomerNote $customerNote, array $modelData): CustomerNote
    {
        return $this->update($customerNote, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        $rules = [
            'note'      => ['sometimes', 'string', 'max:1024'],
        ];

        if (!$this->strict) {
            $rules['note'] = ['sometimes', 'string', 'max:4096'];
        }

        return $rules;
    }


    public function asController(Organisation $organisation, Shop $shop, CustomerNote $customerNote, ActionRequest $request): CustomerNote
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($customerNote, $this->validatedData);
    }

    public function action(CustomerNote $customerNote, array $modelData, int $hydratorsDelay = 0, bool $strict = true): CustomerNote
    {
        $this->asAction = true;
        $this->strict = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->setRawAttributes($modelData);
        $this->initialisationFromShop($customerNote->shop, $modelData);

        return $this->handle($customerNote, $this->validatedData);
    }


}

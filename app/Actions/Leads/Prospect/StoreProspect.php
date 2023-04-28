<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Leads\Prospect;

use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Leads\Prospect\Hydrators\ProspectHydrateUniversalSearch;
use App\Models\Leads\Prospect;
use App\Models\Marketing\Shop;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreProspect
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;

    public function handle(Shop $shop, array $modelData, array $addressesData = []): Prospect
    {
        /** @var Prospect $prospect */
        $prospect = $shop->prospects()->create($modelData);

        StoreAddressAttachToModel::run($prospect, $addressesData, ['scope' => 'contact']);
        $prospect->location = $prospect->getLocation();
        $prospect->save();

        // TODO Create Hydrators actions
        //ShopHydrateProspects::dispatch($prospect->shop);
        //TenantHydrateProspects::dispatch(app('currentTenant'));
        ProspectHydrateUniversalSearch::dispatch($prospect);
        return $prospect;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("shops.customers.edit");
    }

    public function rules(): array
    {
        return [
            'contact_name'              => ['nullable', 'string', 'max:255'],
            'company_name'              => ['nullable', 'string', 'max:255'],
            'email'                     => ['nullable', 'email'],
            'phone'                     => ['nullable', 'string'],
            'website'                   => ['nullable', 'active_url'],
        ];
    }

    public function action(Shop $shop, array $objectData): Prospect
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shop, $validatedData);
    }

}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jan 2024 11:06:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment;

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithShopRules;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreFulfilmentFromUI extends OrgAction
{
    use WithShopRules;


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo('org-supervisor.'.$this->organisation->id);
    }

    public function rules(ActionRequest $request): array
    {
        return $this->getStoreShopRules();
    }


    public function asController(Organisation $organisation, ActionRequest $request): Fulfilment
    {
        $this->initialisation($organisation, $request);
        $shop = StoreShop::make()->action($organisation, $this->validatedData);

        return $shop->fulfilment;
    }




    public function htmlResponse(Fulfilment $fulfilment): RedirectResponse
    {
        return Redirect::route(
            'grp.org.fulfilments.show.operations.dashboard',
            [
                $this->organisation->slug,
                $fulfilment->slug
            ]
        );
    }

}

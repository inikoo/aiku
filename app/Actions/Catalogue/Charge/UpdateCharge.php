<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Catalogue\Charge;

use App\Actions\Catalogue\Charge\Hydrators\ChargeHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateCharges;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCharges;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Catalogue\Charge;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class UpdateCharge extends OrgAction
{
    use WithActionUpdate;


    private Charge $charge;

    public function handle(Charge $charge, array $modelData): Charge
    {
        $charge = $this->update($charge, $modelData, ['data']);

        if ($charge->wasChanged(['type', 'status'])) {
            OrganisationHydrateCharges::dispatch($charge->organisation);
            GroupHydrateCharges::dispatch($charge->group);
        }


        ChargeHydrateUniversalSearch::dispatch($charge);


        return $charge;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [];
    }

    public function asController(Organisation $organisation, Charge $charge, ActionRequest $request): Charge
    {
        $this->charge = $charge;
        $this->initialisation($charge->organisation, $request);

        return $this->handle($charge, $this->validatedData);
    }

    public function action(Charge $charge, array $modelData): Charge
    {
        $this->asAction        = true;
        $this->charge          = $charge;
        $this->initialisation($charge->organisation, $modelData);

        return $this->handle($charge, $this->validatedData);
    }


}

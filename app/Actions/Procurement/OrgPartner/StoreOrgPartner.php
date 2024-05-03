<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 14:45:14 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgPartner;

use App\Actions\OrgAction;
use App\Models\Procurement\OrgPartner;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgPartner extends OrgAction
{
    public function handle(Organisation $organisation, Organisation $partner, $modelData = []): OrgPartner
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        data_set($modelData, 'organisation_id', $organisation->id);
        data_set($modelData, 'partner_id', $partner->id);

        data_set($modelData, 'status', $partner->status, false);


        /** @var OrgPartner $orgPartner */
        $orgPartner = OrgPartner::create($modelData);
        $orgPartner->stats()->create();


        return $orgPartner;
    }


    public function rules(ActionRequest $request): array
    {
        return [
        ];
    }

    public function action(Organisation $organisation, Organisation $partner, $modelData = [], $hydratorDelay = 0): OrgPartner
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorDelay;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $partner, $this->validatedData);
    }


}

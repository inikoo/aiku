<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\Artefact;

use App\Models\Inventory\OrgStock;
use App\Models\Production\Artefact;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOrgStockArtefact
{
    use AsObject;

    /**
     * The artefact a manufacturing hub makes this stock from, for users allowed to see its production.
     */
    public function handle(OrgStock $orgStock, ?User $user): ?Artefact
    {
        if (!$user || !$orgStock->organisation->is_manufacturing_hub) {
            return null;
        }

        $artefact = Artefact::where('org_stock_id', $orgStock->id)->first();

        if (!$artefact || !$user->authTo([
            'org-supervisor.'.$orgStock->organisation_id,
            'productions-view.'.$orgStock->organisation_id,
            "productions_operations.{$artefact->production_id}.view",
            "productions_operations.{$artefact->production_id}.orchestrate",
            "productions_rd.{$artefact->production_id}.view",
            "productions_procurement.{$artefact->production_id}.view",
        ])) {
            return null;
        }

        return $artefact;
    }
}

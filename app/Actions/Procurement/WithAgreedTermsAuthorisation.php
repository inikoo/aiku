<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 02:10:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement;

use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\User;

/**
 * Agreed terms and procurement notes hang from group records (supplier, supplier product, agent)
 * that every organisation shares, so the right to write them comes from any buying organisation
 * of the group. Agent organisations never write them: they only read what is marked for agents.
 */
trait WithAgreedTermsAuthorisation
{
    protected function canWriteProcurementNotes(User $user, Group $group, ?int $organisationId = null): bool
    {
        if ($organisationId === null && $user->authTo('supply-chain.edit')) {
            return true;
        }

        return $this->hasProcurementPermission($user, $group, $organisationId, fn (int $id) => "procurement.$id.edit");
    }

    protected function isProcurementSupervisor(User $user, Group $group, ?int $organisationId = null): bool
    {
        return $this->hasProcurementPermission($user, $group, $organisationId, fn (int $id) => "org-supervisor.$id.procurement");
    }

    private function hasProcurementPermission(User $user, Group $group, ?int $organisationId, callable $permission): bool
    {
        $permissions = $group->organisations()
            ->where('type', '!=', OrganisationTypeEnum::AGENT)
            ->when($organisationId, fn ($query) => $query->where('id', $organisationId))
            ->pluck('id')
            ->map($permission)
            ->all();

        return $permissions !== [] && $user->authTo($permissions);
    }
}

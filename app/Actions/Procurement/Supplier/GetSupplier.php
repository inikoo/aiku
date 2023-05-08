<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 03 May 2023 13:26:09 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier;

use App\Actions\Procurement\Supplier\UI\IndexSuppliers;
use App\Models\Procurement\Agent;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class GetSupplier
{
    use AsAction;

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(Agent $agent)
    {
        $currentTenant = app('currentTenant');
        if($agent->where('owner_id', $currentTenant->id)->exists() && $agent->is_private) {
            return IndexSuppliers::run($agent);
        } elseif(!$agent->is_private) {
            return IndexSuppliers::run($agent);
        }

        throw ValidationException::withMessages(["You can't view this supplier"]);
    }
}

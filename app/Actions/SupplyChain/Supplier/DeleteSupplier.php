<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 May 2024 19:44:10 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier;

use App\Models\SupplyChain\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteSupplier
{
    use AsController;
    use WithAttributes;

    public function handle(Agent $agent): Agent
    {
        $agent->delete();

        return $agent;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("supply-chain.edit");
    }

    public function asController(Agent $agent, ActionRequest $request): Agent
    {
        $request->validate();

        return $this->handle($agent);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('grp.supply-chain.agents.index');
    }

}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 16:20:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Models\SupplyChain\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteMarketplaceAgent
{
    use AsController;
    use WithAttributes;

    public function handle(Agent $marketplaceAgent): Agent
    {
        $marketplaceAgent->delete();

        return $marketplaceAgent;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("procurement.edit");
    }

    public function asController(Agent $marketplaceAgent, ActionRequest $request): Agent
    {
        $request->validate();

        return $this->handle($marketplaceAgent);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('grp.org.procurement.marketplace.agents.index');
    }

}

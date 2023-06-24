<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Marketplace\Agent;

use App\Models\Procurement\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteMarketplaceAgent
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
        return $request->user()->hasPermissionTo("procurement.edit");
    }

    public function asController(Agent $marketplaceAgent, ActionRequest $request): Agent
    {
        $request->validate();

        return $this->handle($marketplaceAgent);
    }

    public function htmlResponse(Agent $agent): RedirectResponse
    {
        return Redirect::route('procurement.marketplace.agents.show', $agent->slug);
    }

}

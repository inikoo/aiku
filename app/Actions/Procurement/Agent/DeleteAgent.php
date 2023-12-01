<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Models\Procurement\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteAgent
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

    public function asController(Agent $agent, ActionRequest $request): Agent
    {
        $request->validate();

        return $this->handle($agent);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('grp.procurement.agents.index');
    }

}

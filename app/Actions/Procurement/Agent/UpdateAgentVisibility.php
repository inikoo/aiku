<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 03 May 2023 13:26:09 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Actions\WithActionUpdate;
use App\Http\Resources\Procurement\AgentResource;
use App\Models\Procurement\Agent;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class UpdateAgentVisibility
{
    use WithActionUpdate;

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(Agent $agent, array $modelData): Agent| ValidationException
    {
        if($agent->is_private) {
            return $this->update($agent, $modelData);
        }

        throw ValidationException::withMessages(["You can't change visibility to private"]);
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
        //        return $request->user()->hasPermissionTo("procurement.edit");
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function action(Agent $agent, bool $isPrivate): Agent|ValidationException
    {
        return $this->handle($agent, [
            'is_private' => $isPrivate
        ]);
    }

    public function asController(Agent $agent, ActionRequest $request): Agent|ValidationException
    {
        $request->validate();
        return $this->handle($agent, $request->all());
    }


    public function jsonResponse(Agent $agent): AgentResource
    {
        return new AgentResource($agent);
    }
}

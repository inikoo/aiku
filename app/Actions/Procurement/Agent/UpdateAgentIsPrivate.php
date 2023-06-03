<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 03 May 2023 13:26:09 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Models\Procurement\Agent;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateAgentIsPrivate
{
    use AsAction;
    use WithAttributes;


    public function handle(Agent $agent, bool $isPrivate): Agent
    {
        $agent->update(
            [
                'is_private' => $isPrivate
            ]
        );


        return $agent;
    }

    public function authorize(Agent $agent, ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("procurement.edit") && app('currentTenant')->id == $agent->owner_id;
    }

    public function rules(): array
    {
        return [
            'is_private' => [
                'required',
                'boolean'
            ]
        ];
    }

    public function asController(Agent $agent, ActionRequest $request): Agent|ValidationException
    {
        $validatedData = $this->validateAttributes();
        $request->validate();
        return $this->handle($agent, Arr::get($validatedData, 'is_private'));
    }


}

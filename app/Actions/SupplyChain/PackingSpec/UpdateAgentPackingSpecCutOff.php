<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 12:50
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\SupplyChain\PackingSpec;

use App\Actions\OrgAction;
use App\Enums\SysAdmin\Authorisation\GroupPermissionsEnum;
use App\Models\SupplyChain\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

/**
 * Sets the day from which an agent's purchase orders cannot be submitted while a product on them has no packing spec.
 * Before that day the missing specs are only a warning; no date means never blocked.
 */
class UpdateAgentPackingSpecCutOff extends OrgAction
{
    public function handle(Agent $agent, ?string $cutOff): Agent
    {
        $settings = $agent->settings ?? [];
        if ($cutOff) {
            Arr::set($settings, GetPurchaseOrderPackingSpecGaps::CUT_OFF_SETTING, $cutOff);
        } else {
            Arr::forget($settings, GetPurchaseOrderPackingSpecGaps::CUT_OFF_SETTING);
        }
        $agent->update(['settings' => $settings]);

        return $agent;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $this->asAction || $request->user()->authTo(GroupPermissionsEnum::SUPPLY_CHAIN_EDIT->value);
    }

    public function rules(): array
    {
        return [
            'cut_off' => ['present', 'nullable', 'date_format:Y-m-d'],
        ];
    }

    public function asController(Agent $agent, ActionRequest $request): RedirectResponse
    {
        $this->initialisationFromGroup($agent->group, $request);
        $this->handle($agent, $this->validatedData['cut_off']);

        return redirect()->back();
    }
}

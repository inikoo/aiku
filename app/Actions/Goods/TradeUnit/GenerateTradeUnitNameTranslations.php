<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\TradeUnit;

use App\Actions\OrgAction;
use App\Models\Goods\TradeUnit;
use App\Models\SysAdmin\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

/**
 * Translations are queued, translating every shop language takes up to half a minute. The edit form
 * and the label window follow the job on the grp.trade-unit channel and reload when it is done.
 */
class GenerateTradeUnitNameTranslations extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo('goods.edit') || $request->user()->authTo('compliance.edit');
    }

    public function rules(): array
    {
        return [
            'include_outdated' => ['sometimes', 'boolean'],
            'trade_units'      => ['sometimes', 'array', 'min:1'],
            'trade_units.*'    => ['integer', Rule::exists('trade_units', 'id')->where('group_id', $this->group->id)],
        ];
    }

    public function asController(TradeUnit $tradeUnit, ActionRequest $request): RedirectResponse|JsonResponse
    {
        $this->initialisationFromGroup($tradeUnit->group, $request);

        TranslateTradeUnitName::queue($tradeUnit, (bool) Arr::get($this->validatedData, 'include_outdated', false));

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json(['queued' => true]);
        }

        return back();
    }

    public function inBulk(ActionRequest $request): RedirectResponse
    {
        $this->initialisationFromGroup(group(), $request);

        $count = $this->queue($this->group, Arr::get($this->validatedData, 'trade_units', []));

        return back()->with('notification', [
            'status'      => 'success',
            'title'       => __('Translating :count trade units', ['count' => $count]),
            'description' => __('Missing names are added in the background over the next few minutes. Names already there are kept.'),
        ]);
    }

    /**
     * @param array<int, int> $tradeUnitIds
     */
    public function queue(Group $group, array $tradeUnitIds): int
    {
        $tradeUnits = TradeUnit::where('group_id', $group->id)->whereIn('id', $tradeUnitIds)->get();

        foreach ($tradeUnits as $tradeUnit) {
            TranslateTradeUnitName::queue($tradeUnit);
        }

        return $tradeUnits->count();
    }
}

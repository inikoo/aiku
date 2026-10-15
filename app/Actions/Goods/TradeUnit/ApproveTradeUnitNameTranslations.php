<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\TradeUnit;

use App\Actions\OrgAction;
use App\Models\Goods\TradeUnit;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

class ApproveTradeUnitNameTranslations extends OrgAction
{
    /**
     * @param array{languages: array<int, string>} $modelData
     */
    public function handle(TradeUnit $tradeUnit, array $modelData): TradeUnit
    {
        TranslateTradeUnitName::make()->approve($tradeUnit, $modelData['languages']);

        return $tradeUnit;
    }

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
            'languages'   => ['required', 'array', 'min:1'],
            'languages.*' => ['string', 'max:8'],
        ];
    }

    public function asController(TradeUnit $tradeUnit, ActionRequest $request): RedirectResponse
    {
        $this->initialisationFromGroup($tradeUnit->group, $request);
        $this->handle($tradeUnit, $this->validatedData);

        return back();
    }

    /**
     * @param array{languages: array<int, string>} $modelData
     */
    public function action(TradeUnit $tradeUnit, array $modelData): TradeUnit
    {
        $this->asAction = true;
        $this->initialisationFromGroup($tradeUnit->group, $modelData);

        return $this->handle($tradeUnit, $this->validatedData);
    }
}

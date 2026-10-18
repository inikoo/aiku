<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\TradeUnit;

use App\Actions\Catalogue\Product\CloneProductAttachmentsFromTradeUnits;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithAttachmentEditAuthorisation;
use App\Models\Goods\TradeUnit;
use Lorisleiva\Actions\ActionRequest;

class StopSharingTradeUnitDocuments extends OrgAction
{
    use WithAttachmentEditAuthorisation;

    private TradeUnit $tradeUnit;

    public function handle(TradeUnit $tradeUnit): TradeUnit
    {
        $tradeUnit->update(['documents_trade_unit_id' => null]);

        foreach ($tradeUnit->products as $product) {
            CloneProductAttachmentsFromTradeUnits::dispatch($product);
        }

        return $tradeUnit;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $this->canChangeAttachments($request->user(), $this->tradeUnit);
    }

    public function action(TradeUnit $tradeUnit): TradeUnit
    {
        $this->asAction = true;
        $this->initialisationFromGroup($tradeUnit->group, []);

        return $this->handle($tradeUnit);
    }

    public function asController(TradeUnit $tradeUnit, ActionRequest $request): void
    {
        $this->tradeUnit = $tradeUnit;
        $this->initialisationFromGroup($tradeUnit->group, $request);

        $this->handle($tradeUnit);
    }
}

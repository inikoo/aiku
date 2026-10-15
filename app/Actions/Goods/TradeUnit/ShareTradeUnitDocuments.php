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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class ShareTradeUnitDocuments extends OrgAction
{
    use WithAttachmentEditAuthorisation;

    private TradeUnit $tradeUnit;

    /**
     * @param  array{trade_units: list<int>}  $modelData
     */
    public function handle(TradeUnit $tradeUnit, array $modelData): TradeUnit
    {
        $sharers = DB::transaction(function () use ($tradeUnit, $modelData) {
            $tradeUnitIds = Arr::get($modelData, 'trade_units');
            $lockedIds    = [$tradeUnit->id, ...$tradeUnitIds];
            TradeUnit::whereIn('id', $lockedIds)->orderBy('id')->lockForUpdate()->get();

            if (TradeUnit::whereKey($tradeUnit->id)->whereNotNull('documents_trade_unit_id')->exists()
                || TradeUnit::whereIn('documents_trade_unit_id', $tradeUnitIds)->exists()
                || TradeUnit::whereIn('id', $tradeUnitIds)->whereNotNull('documents_trade_unit_id')->whereNot('documents_trade_unit_id', $tradeUnit->id)->exists()) {
                throw ValidationException::withMessages(['trade_units' => __('Documents can only be shared one level deep, a trade unit here shares with others or takes from another one')]);
            }

            $sharers = TradeUnit::whereIn('id', $tradeUnitIds)->get();
            foreach ($sharers as $sharer) {
                $sharer->update(['documents_trade_unit_id' => $tradeUnit->id]);
            }

            return $sharers;
        });

        foreach ($sharers as $sharer) {
            foreach ($sharer->products as $product) {
                CloneProductAttachmentsFromTradeUnits::dispatch($product);
            }
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

    public function rules(): array
    {
        return [
            'trade_units'   => ['required', 'array', 'min:1'],
            'trade_units.*' => [
                'integer',
                'distinct',
                Rule::notIn([$this->tradeUnit->id]),
                Rule::exists('trade_units', 'id')->where('group_id', $this->tradeUnit->group_id)->whereNull('deleted_at'),
            ],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if ($this->tradeUnit->documents_trade_unit_id) {
            $validator->errors()->add('trade_units', __(':code takes its documents from another trade unit, share them from that one', ['code' => $this->tradeUnit->code]));

            return;
        }

        $tradeUnitIds = Arr::wrap($this->get('trade_units'));

        $sources = TradeUnit::whereIn('id', $tradeUnitIds)->whereHas('documentsSharers')->pluck('code');
        if ($sources->isNotEmpty()) {
            $validator->errors()->add('trade_units', __('Other trade units take their documents from :codes', ['codes' => $sources->implode(', ')]));
        }

        $sharingElsewhere = TradeUnit::whereIn('id', $tradeUnitIds)->whereNotNull('documents_trade_unit_id')->whereNot('documents_trade_unit_id', $this->tradeUnit->id)->pluck('code');
        if ($sharingElsewhere->isNotEmpty()) {
            $validator->errors()->add('trade_units', __(':codes already take their documents from another trade unit, stop that first', ['codes' => $sharingElsewhere->implode(', ')]));
        }
    }

    public function action(TradeUnit $tradeUnit, array $modelData): TradeUnit
    {
        $this->asAction  = true;
        $this->tradeUnit = $tradeUnit;
        $this->initialisationFromGroup($tradeUnit->group, $modelData);

        return $this->handle($tradeUnit, $this->validatedData);
    }

    public function asController(TradeUnit $tradeUnit, ActionRequest $request): void
    {
        $this->tradeUnit = $tradeUnit;
        $this->initialisationFromGroup($tradeUnit->group, $request);

        $this->handle($tradeUnit, $this->validatedData);
    }
}

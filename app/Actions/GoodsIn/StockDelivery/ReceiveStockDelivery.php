<?php

namespace App\Actions\GoodsIn\StockDelivery;

use App\Actions\Traits\Authorisations\WithGoodsInBookInAuthorisation;
use App\Actions\GoodsIn\StockDelivery\Traits\HasStockDeliveryHydrators;
use App\Actions\GoodsIn\StockDeliveryItem\Traits\WithStockDeliveryItemStatePropagation;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryStateEnum;
use App\Enums\GoodsIn\StockDeliveryItem\StockDeliveryItemStateEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\GoodsIn\StockDeliveryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ReceiveStockDelivery extends OrgAction
{
    use WithGoodsInBookInAuthorisation;
    use AsAction;
    use HasStockDeliveryHydrators;
    use WithActionUpdate;
    use WithStockDeliveryItemStatePropagation;

    public int $hydratorsDelay = 0;

    private StockDelivery $stockDelivery;

    private const RECEIVABLE_STATES = [
        StockDeliveryStateEnum::IN_PROCESS,
        StockDeliveryStateEnum::CONFIRMED,
        StockDeliveryStateEnum::READY_TO_SHIP,
        StockDeliveryStateEnum::DISPATCHED,
    ];

    public function rules(): array
    {
        return [
            'reason' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->asAction && ($missing = $this->stockDelivery->costsMissingForPutAway()) && mb_strlen(trim((string) $this->get('reason'))) < 5) {
            $validator->errors()->add('reason', __('The costs of this delivery are missing (:costs). Say why it is being received without them', ['costs' => implode(', ', $missing)]));
        }
        if (!$this->asAction && $this->stockDelivery->isManagedByPartner() && $this->stockDelivery->state !== StockDeliveryStateEnum::DISPATCHED) {
            $validator->errors()->add('state', __('This delivery is managed by the partner until they dispatch it'));
        }
        if (!in_array($this->stockDelivery->state, self::RECEIVABLE_STATES, true)) {
            $validator->errors()->add('state', __('You can not receive this stock delivery with state :state', ['state' => $this->stockDelivery->state->value]));
        }
        if ($this->stockDelivery->hasNoProducts()) {
            $validator->errors()->add('state', __('This delivery has no products to receive, cancel it instead'));
        }
    }

    public function handle(StockDelivery $stockDelivery): StockDelivery
    {
        $stockDelivery->update([
            'state'       => StockDeliveryStateEnum::RECEIVED,
            'received_at' => now(),
        ]);

        $stockDelivery->items()
            ->where('state', '!=', StockDeliveryItemStateEnum::CANCELLED)
            ->get()
            ->each(fn (StockDeliveryItem $stockDeliveryItem) => $this->receiveItem($stockDeliveryItem));

        UpdatePurchaseOrdersDeliveryStateFromStockDelivery::run($stockDelivery);

        $this->runStockDeliveryHydrators($stockDelivery);

        return $stockDelivery;
    }

    private function receiveItem(StockDeliveryItem $stockDeliveryItem): void
    {
        if ($stockDeliveryItem->state === StockDeliveryItemStateEnum::RECEIVED) {
            return;
        }

        $stockDeliveryItem = $this->update($stockDeliveryItem, [
            'state'       => StockDeliveryItemStateEnum::RECEIVED,
            'received_at' => now(),
        ]);

        $this->propagateStockDeliveryItemStateChange($stockDeliveryItem);
    }

    public function asController(StockDelivery $stockDelivery, ActionRequest $request): StockDelivery
    {
        $this->stockDelivery = $stockDelivery;
        $this->initialisation($stockDelivery->organisation, $request);

        $missing       = $stockDelivery->costsMissingForPutAway();
        $stockDelivery = $this->handle($stockDelivery);

        if ($missing) {
            rescue(fn () => ReportStockDeliveryReceivedWithoutCosts::run($stockDelivery, $request->user(), $missing, trim((string) $this->get('reason'))));
        }

        return $stockDelivery;
    }

    public function action(StockDelivery $stockDelivery): StockDelivery
    {
        $this->asAction      = true;
        $this->stockDelivery = $stockDelivery;
        $this->initialisation($stockDelivery->organisation, []);

        return $this->handle($stockDelivery);
    }

    public function htmlResponse(StockDelivery $stockDelivery): RedirectResponse
    {
        return redirect()->back();
    }
}

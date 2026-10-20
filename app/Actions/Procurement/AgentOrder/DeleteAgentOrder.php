<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 10:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\AgentOrder;

use App\Actions\GoodsIn\StockDelivery\DeleteStockDelivery;
use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrder\DeletePurchaseOrder;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\GoodsIn\StockDeliveryItem;
use App\Models\Procurement\AgentWarehouseStock;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

/**
 * Deletes the whole agent order: every supplier order in it and the agent's containers carrying them, refused once
 * any of that stock is booked in or money is recorded against it. The person deleting types that they accept the
 * responsibility.
 */
class DeleteAgentOrder extends OrgAction
{
    use WithProcurementEditAuthorisation;

    /**
     * @return array<int, string>
     */
    public static function refusals(OrgAgent $orgAgent, string $agentOrderReference): array
    {
        $purchaseOrders   = PurchaseOrder::inAgentOrder($orgAgent->organisation_id, $orgAgent->agent_id, $agentOrderReference)->get();
        $purchaseOrderIds = $purchaseOrders->pluck('id')->all();

        return array_values(array_filter([
            $purchaseOrders->isEmpty() ? __('There is nothing to delete in :reference', ['reference' => $agentOrderReference]) : null,
            ...$purchaseOrders->filter(fn (PurchaseOrder $purchaseOrder) => $purchaseOrder->state === PurchaseOrderStateEnum::SETTLED)
                ->map(fn (PurchaseOrder $purchaseOrder) => __(':reference is settled', ['reference' => $purchaseOrder->reference])),
            ...$purchaseOrders->filter(fn (PurchaseOrder $purchaseOrder) => $purchaseOrder->deposit_paid_at || $purchaseOrder->deposits()->exists())
                ->map(fn (PurchaseOrder $purchaseOrder) => __(':reference has deposits requested or paid', ['reference' => $purchaseOrder->reference])),
            AgentWarehouseStock::whereIn('purchase_order_id', $purchaseOrderIds)->exists() ? __('The agent already has goods of :reference in its warehouse', ['reference' => $agentOrderReference]) : null,
            ...self::stockDeliveries($purchaseOrderIds)->map(fn (StockDelivery $stockDelivery) => self::stockDeliveryRefusal($stockDelivery, $purchaseOrderIds)),
        ]));
    }

    /**
     * @param  array<int, int>  $purchaseOrderIds
     */
    private static function stockDeliveryRefusal(StockDelivery $stockDelivery, array $purchaseOrderIds): ?string
    {
        return match (true) {
            !DeleteStockDelivery::isAgentDelivery($stockDelivery)                                       => __(':reference is not a container of the agent', ['reference' => $stockDelivery->reference]),
            $stockDelivery->purchaseOrders()->whereNotIn('purchase_orders.id', $purchaseOrderIds)->exists() => __(':reference also carries other orders, take this order out of it first', ['reference' => $stockDelivery->reference]),
            in_array($stockDelivery->state, [StockDeliveryStateEnum::CANCELLED, StockDeliveryStateEnum::NOT_RECEIVED], true) => DeleteStockDelivery::recordsRefusal($stockDelivery),
            default                                                                                       => DeleteStockDelivery::agentDeliveryRefusal($stockDelivery),
        };
    }

    /**
     * Every delivery carrying these orders, cancelled ones included.
     *
     * @param  array<int, int>  $purchaseOrderIds
     *
     * @return Collection<int, StockDelivery>
     */
    private static function stockDeliveries(array $purchaseOrderIds, bool $lock = false): Collection
    {
        return StockDelivery::query()
            ->whereHas('purchaseOrders', fn ($query) => $query->whereIn('purchase_orders.id', $purchaseOrderIds))
            ->when($lock, fn ($query) => $query->lockForUpdate())
            ->get();
    }

    public function handle(OrgAgent $orgAgent, string $agentOrderReference, string $reason): int
    {
        return DB::transaction(function () use ($orgAgent, $agentOrderReference, $reason) {
            $purchaseOrders = PurchaseOrder::inAgentOrder($orgAgent->organisation_id, $orgAgent->agent_id, $agentOrderReference)->lockForUpdate()->get();
            $stockDeliveries = self::stockDeliveries($purchaseOrders->pluck('id')->all(), lock: true);
            StockDeliveryItem::whereIn('stock_delivery_id', $stockDeliveries->pluck('id'))->lockForUpdate()->get(['id']);

            if ($refusals = self::refusals($orgAgent, $agentOrderReference)) {
                throw ValidationException::withMessages(['agent_order' => implode(' ', $refusals)]);
            }

            $stockDeliveries->reject(fn (StockDelivery $stockDelivery) => in_array($stockDelivery->state, [StockDeliveryStateEnum::CANCELLED, StockDeliveryStateEnum::NOT_RECEIVED], true))
                ->each(fn (StockDelivery $stockDelivery) => DeleteStockDelivery::make()->action($stockDelivery, $reason));
            $purchaseOrders->each(function (PurchaseOrder $purchaseOrder) use ($reason) {
                DeleteStockDelivery::recordDeletion($purchaseOrder, $reason);
                DeletePurchaseOrder::make()->handle($purchaseOrder);
            });

            return $purchaseOrders->count();
        });
    }

    public function rules(): array
    {
        return [
            'agent_order_reference' => ['required', 'string'],
            'responsibility'        => ['required', 'string', 'max:255'],
            'reason'                => ['required', 'string', 'min:'.DeleteStockDelivery::MIN_REASON_LENGTH, 'max:1000'],
        ];
    }

    public function asController(OrgAgent $orgAgent, ActionRequest $request): int
    {
        $this->initialisation($orgAgent->organisation, $request);

        if (!DeleteStockDelivery::acceptsResponsibility($this->validatedData['responsibility'])) {
            throw ValidationException::withMessages(['responsibility' => __('Type ":phrase" to delete it', ['phrase' => DeleteStockDelivery::RESPONSIBILITY_PHRASE])]);
        }

        return $this->handle($orgAgent, $this->validatedData['agent_order_reference'], trim($this->validatedData['reason']));
    }

    public function action(OrgAgent $orgAgent, string $agentOrderReference, string $reason): int
    {
        $this->asAction = true;
        $this->initialisation($orgAgent->organisation, ['agent_order_reference' => $agentOrderReference, 'responsibility' => DeleteStockDelivery::RESPONSIBILITY_PHRASE, 'reason' => $reason]);

        return $this->handle($orgAgent, $agentOrderReference, $reason);
    }

    public function htmlResponse(int $deleted, ActionRequest $request): RedirectResponse
    {
        $orgAgent = $request->route('orgAgent');

        return Redirect::route('grp.org.procurement.org_agents.show.agent_orders.index', [$orgAgent->organisation->slug, $orgAgent->slug]);
    }
}

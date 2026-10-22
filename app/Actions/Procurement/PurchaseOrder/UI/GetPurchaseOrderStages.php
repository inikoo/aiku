<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 09:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder\UI;

use App\Actions\Procurement\AgentGoodsReceipt\StoreAgentGoodsReceipt;
use App\Actions\Procurement\PurchaseOrder\MarkPurchaseOrderStage;
use App\Actions\Procurement\AgentGoodsReceipt\StoreAgentGoodsReceipt;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryStateEnum;
use App\Enums\Procurement\AgentGoodsReceipt\AgentGoodsReceiptStateEnum;
use App\Enums\Procurement\AgentWarehouseStock\AgentWarehouseStockStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderAttachmentScopeEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStageEnum as Stage;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\Procurement\AgentGoodsReceipt;
use App\Models\Procurement\AgentGoodsReceiptItem;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\User;
use App\Actions\SupplyChain\UI\ShowSupplyChainPurchaseOrderJourney;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Where a purchase order stands in its stages. A delivery stage counts as reached only when every live
 * delivery of the order reached it, dated by the last of them, so the order shows its slowest delivery.
 * In warehouse is reached when the first goods reach the agent warehouse; it is checked and packed once every
 * line is received in full and all of it is checked, or packed. It shows only on orders through an agent that
 * receives goods in its own warehouse, and, as nobody records it by hand, marking a later stage never asks for it.
 */
class GetPurchaseOrderStages
{
    use AsObject;

    private const array WAREHOUSE_COLUMNS = ['warehouse_arrived_at', 'warehouse_checked_at', 'warehouse_packed_at'];

    private array $warehouseDates = [];

    private array $usesAgentWarehouse = [];

    private const array SHIPPED_STATES = [
        StockDeliveryStateEnum::DISPATCHED,
        StockDeliveryStateEnum::RECEIVED,
        StockDeliveryStateEnum::CHECKED,
        StockDeliveryStateEnum::BOOKING_IN,
        StockDeliveryStateEnum::BOOKED_IN,
        StockDeliveryStateEnum::PLACED,
    ];

    private const array RECEIVED_STATES = [
        StockDeliveryStateEnum::RECEIVED,
        StockDeliveryStateEnum::CHECKED,
        StockDeliveryStateEnum::BOOKING_IN,
        StockDeliveryStateEnum::BOOKED_IN,
        StockDeliveryStateEnum::PLACED,
    ];

    /**
     * @return array<string, array{recorded: bool, reached: bool, date: ?Carbon, user_id: ?int, deliveries: ?array{reached: int, total: int}}>
     */
    public function handle(PurchaseOrder $purchaseOrder): array
    {
        $deliveries = $this->liveDeliveries($purchaseOrder);

        $stages = [];
        foreach ($this->stagesOf($purchaseOrder) as $stage) {
            $stages[$stage->value] = match (true) {
                $stage->isDeliveryStage()     => $this->deliveryStage($purchaseOrder, $stage, $deliveries),
                $stage === Stage::IN_WAREHOUSE => [
                    'recorded'   => $this->warehouseDates($purchaseOrder)['warehouse_arrived_at'] !== null,
                    'date'       => $this->warehouseDates($purchaseOrder)['warehouse_arrived_at'],
                    'user_id'    => null,
                    'deliveries' => null,
                ],
                default => [
                    'recorded'   => $purchaseOrder->{$stage->column()} !== null
                        || ($stage === Stage::SUBMITTED && $purchaseOrder->state !== PurchaseOrderStateEnum::IN_PROCESS)
                        || ($stage === Stage::DEPOSIT_PAID && $this->depositNotRequired($purchaseOrder)),
                    'date'       => $purchaseOrder->{$stage->column()},
                    'user_id'    => $purchaseOrder->stageMarkUserId($stage->column()),
                    'deliveries' => null,
                ],
            };
        }

        $laterReached = false;
        foreach (array_reverse(array_keys($stages)) as $key) {
            $laterReached               = $laterReached || $stages[$key]['recorded'];
            $stages[$key]['reached'] = $laterReached;
        }

        return $stages;
    }

    /**
     * What marking this date passes over without it being recorded: earlier stages, and the stage's gate.
     *
     * @return string[]
     */
    public function skippedBy(PurchaseOrder $purchaseOrder, string $field, array $stages): array
    {
        $stage   = Stage::ofField($field);
        $skipped = [];

        if ($field === $stage->column()) {
            foreach (Stage::cases() as $earlier) {
                if ($earlier->position() >= $stage->position()) {
                    break;
                }
                if ($earlier !== Stage::IN_WAREHOUSE && isset($stages[$earlier->value]) && !$stages[$earlier->value]['recorded']) {
                    $skipped[] = Stage::labels()[$earlier->value];
                }
            }
        }

        foreach (Stage::FIELD_GATES[$field] ?? [] as $gate) {
            if ($gate instanceof PurchaseOrderAttachmentScopeEnum) {
                if (!$this->evidenceCount($purchaseOrder, $gate)) {
                    $skipped[] = PurchaseOrderAttachmentScopeEnum::labels()[$gate->value];
                }
            } elseif ($purchaseOrder->{$gate} === null) {
                $skipped[] = Stage::fieldLabels()[$gate];
            }
        }

        if ($field === 'qc_passed_at') {
            array_push($skipped, ...GetPurchaseOrderCompliance::make()->missingSupplierOnly($purchaseOrder));
        }

        if ($field === 'compliance_complete_at') {
            array_push($skipped, ...GetPurchaseOrderCompliance::make()->missingRequired($purchaseOrder));
        }

        return $skipped;
    }

    /**
     * No deposit is owed when the buyer set it to none, or, on an order straight to a supplier, when none was set.
     */
    public function depositNotRequired(PurchaseOrder $purchaseOrder): bool
    {
        if ($purchaseOrder->deposit_paid_at !== null) {
            return false;
        }

        return $purchaseOrder->deposit_amount === null
            ? !$purchaseOrder->isAgentOrder()
            : (float) $purchaseOrder->deposit_amount == 0;
    }

    public function evidenceCount(PurchaseOrder $purchaseOrder, PurchaseOrderAttachmentScopeEnum $scope): int
    {
        return $purchaseOrder->attachments()->wherePivot('scope', $scope->value)->count();
    }

    /**
     * @param string[] $markableFields
     */
    public function forUi(PurchaseOrder $purchaseOrder, array $markableFields): array
    {
        $stages     = $this->handle($purchaseOrder);
        $deliveries = $this->liveDeliveries($purchaseOrder);
        $fieldLabels = Stage::fieldLabels();

        $steps = [];
        foreach ($this->stagesOf($purchaseOrder) as $stage) {
            foreach ($stage->steps() as $column) {
                $steps[$stage->value][] = $this->step($purchaseOrder, $deliveries, $column);
            }
        }

        $userIds = collect($stages)->pluck('user_id')
            ->merge(collect($steps)->flatten(1)->pluck('user_id'))
            ->filter()->unique();
        $names = $userIds->isEmpty() ? collect() : User::whereIn('id', $userIds)->get(['id', 'contact_name', 'username'])
            ->mapWithKeys(fn (User $user) => [$user->id => $user->contact_name ?: $user->username]);

        $current  = collect($stages)->filter(fn (array $stage) => $stage['reached'])->keys()->last();
        $expected = $this->expectedDates($purchaseOrder);
        $today    = now()->startOfDay();
        $confirmsOrder = MarkPurchaseOrderStage::confirmsOrder($purchaseOrder, $today) && !$purchaseOrder->numberPendingPriceChanges();

        return collect($this->stagesOf($purchaseOrder))->values()->map(fn (Stage $stage, int $index) => [
            'key'         => $stage->value,
            'number'      => $index + 1,
            'label'       => Stage::labels()[$stage->value],
            'description' => Stage::descriptions()[$stage->value],
            'reached'     => $stages[$stage->value]['reached'],
            'current'     => $stage->value === $current,
            'date'        => $stages[$stage->value]['date'],
            'by'          => $names->get($stages[$stage->value]['user_id']),
            'expected'    => $expected[$stage->value] ?? null,
            'days_late'   => !$stages[$stage->value]['reached'] && isset($expected[$stage->value]) && Carbon::parse($expected[$stage->value])->lt($today)
                ? (int) Carbon::parse($expected[$stage->value])->diffInDays($today)
                : 0,
            'deliveries'  => $stages[$stage->value]['deliveries'],
            'note'        => $stage === Stage::SUBMITTED && $purchaseOrder->agent_reference ? __('Agent\'s number: :number', ['number' => $purchaseOrder->agent_reference]) : null,
            'not_required' => $stage === Stage::DEPOSIT_PAID && $this->depositNotRequired($purchaseOrder),
            'steps'       => collect($steps[$stage->value] ?? [])->map(fn (array $step) => [
                'key'   => $step['key'],
                'label' => $step['label'],
                'date'  => $step['date'],
                'by'    => $names->get($step['user_id']),
                'note'  => $this->stepNote($step, $steps[$stage->value] ?? []),
            ])->all(),
            'evidence'    => collect($stage->evidence())->map(fn (PurchaseOrderAttachmentScopeEnum $scope) => [
                'scope' => $scope->value,
                'label' => PurchaseOrderAttachmentScopeEnum::labels()[$scope->value],
                'count' => $this->evidenceCount($purchaseOrder, $scope),
            ])->all(),
            'marks'       => collect(Stage::MARKABLE_FIELDS)
                ->filter(
                    fn (Stage $fieldStage, string $field) => $fieldStage === $stage && in_array($field, $markableFields)
                )
                ->keys()
                ->map(fn (string $field) => [
                    'field'   => $field,
                    'label'   => $confirmsOrder && $field === 'production_started_at' && $purchaseOrder->{$field} === null ? __('Confirm and Start Production') : $fieldLabels[$field],
                    'confirms_order' => $confirmsOrder && $purchaseOrder->{$field} === null,
                    'date'    => $purchaseOrder->{$field},
                    'skipped' => $this->skippedBy($purchaseOrder, $field, $stages),
                    'planned' => in_array($field, Stage::PLANNED_FIELDS),
                    'suggested' => $field === 'approved_ready_at' ? $purchaseOrder->proposed_ready_at : null,
                ])->values()->all(),
        ])->all();
    }

    /**
     * Each stage's expected date, from the plan the PO journey board draws for the order: the agent's or
     * supplier's lead time stretched over the stages, and the approved ready date for QC. The board works the
     * shipping date back from the arrival estimate, so a stage is never expected before the one ahead of it.
     *
     * @return array<string, string>
     */
    public function expectedDates(PurchaseOrder $purchaseOrder): array
    {
        $ribbon = ShowSupplyChainPurchaseOrderJourney::make()->ribbons($purchaseOrder->group, $purchaseOrder->id)->first();
        if (!$ribbon) {
            return [];
        }

        $planned  = collect($ribbon['segments'])->mapWithKeys(fn (array $segment) => [$segment['key'] => $segment['done_at'] ?: $segment['planned_at']]);
        $expected = [];
        $previous = null;

        foreach (Stage::cases() as $stage) {
            $date = $planned->get($stage->value);
            if ($date) {
                $previous                = $previous ? max($previous, $date) : $date;
                $expected[$stage->value] = $previous;
            }
        }

        return $expected;
    }

    /**
     * How long the goods took from placed in stock to online on the website.
     */
    private function stepNote(array $step, array $stageSteps): ?string
    {
        $placedAt = collect($stageSteps)->firstWhere('key', 'placed_at')['date'] ?? null;
        if ($step['key'] !== 'online_at' || !$step['date'] || !$placedAt) {
            return null;
        }

        $days = (int) Carbon::parse($placedAt)->startOfDay()->diffInDays(Carbon::parse($step['date'])->startOfDay());

        return trans_choice(':count day after placed|:count days after placed', $days, ['count' => $days]);
    }

    /**
     * @return Stage[]
     */
    private function stagesOf(PurchaseOrder $purchaseOrder): array
    {
        $usesAgentWarehouse = $this->usesAgentWarehouse[$purchaseOrder->id]
            ??= $purchaseOrder->isAgentOrder() && AgentGoodsReceipt::where('agent_id', $purchaseOrder->agent_id)->where('state', AgentGoodsReceiptStateEnum::RECEIVED)->exists();

        return array_values(array_filter(Stage::cases(), fn (Stage $stage) => $stage !== Stage::IN_WAREHOUSE || $usesAgentWarehouse));
    }

    /**
     * @return array{warehouse_arrived_at: ?Carbon, warehouse_checked_at: ?Carbon, warehouse_packed_at: ?Carbon}
     */
    private function warehouseDates(PurchaseOrder $purchaseOrder): array
    {
        return $this->warehouseDates[$purchaseOrder->id] ??= $this->computeWarehouseDates($purchaseOrder);
    }

    /**
     * Packed has no date of its own: the warehouse stock's last change is when it was packed, as a check can not
     * change it afterwards.
     */
    private function computeWarehouseDates(PurchaseOrder $purchaseOrder): array
    {
        $items = AgentGoodsReceiptItem::where('purchase_order_id', $purchaseOrder->id)
            ->whereHas('agentGoodsReceipt', fn ($query) => $query->where('state', AgentGoodsReceiptStateEnum::RECEIVED))
            ->with(['agentGoodsReceipt:id,received_at', 'agentWarehouseStock:id,state,quantity,updated_at'])
            ->get();

        $dates = array_fill_keys(self::WAREHOUSE_COLUMNS, null);
        if ($items->isEmpty()) {
            return $dates;
        }

        $dates['warehouse_arrived_at'] = Carbon::parse($items->min(fn (AgentGoodsReceiptItem $item) => $item->agentGoodsReceipt->received_at));
        if (!StoreAgentGoodsReceipt::make()->isReceivedInFull($purchaseOrder)) {
            return $dates;
        }

        if ($items->every(fn (AgentGoodsReceiptItem $item) => $item->checked_at !== null)) {
            $dates['warehouse_checked_at'] = $items->max('checked_at');
        }

        $stocks = $items->pluck('agentWarehouseStock')->filter(fn ($stock) => $stock && (float) $stock->quantity > 0);
        if ($stocks->isNotEmpty() && $stocks->every(fn ($stock) => $stock->state !== AgentWarehouseStockStateEnum::RECEIVED)) {
            $dates['warehouse_packed_at'] = $stocks->max('updated_at');
        }

        return $dates;
    }

    private function liveDeliveries(PurchaseOrder $purchaseOrder): Collection
    {
        return $purchaseOrder->stockDeliveries()
            ->whereNotIn('stock_deliveries.state', [StockDeliveryStateEnum::CANCELLED, StockDeliveryStateEnum::NOT_RECEIVED])
            ->get();
    }

    /**
     * Without a live delivery the order's own delivery state stands, as on the PO journey board, and its settled
     * date is the best date known for the goods arriving.
     */
    private function deliveryStage(PurchaseOrder $purchaseOrder, Stage $stage, Collection $deliveries): array
    {
        if ($deliveries->isEmpty()) {
            $state   = StockDeliveryStateEnum::tryFrom((string) $purchaseOrder->delivery_state?->value);
            $reached = $state && ($stage === Stage::SHIPPED ? in_array($state, self::SHIPPED_STATES) : in_array($state, self::RECEIVED_STATES));

            return [
                'recorded'   => $reached,
                'date'       => $reached && $stage === Stage::RECEIVED ? $purchaseOrder->settled_at : null,
                'user_id'    => null,
                'deliveries' => ['reached' => 0, 'total' => 0],
            ];
        }

        $reached = $deliveries->filter(fn (StockDelivery $delivery) => $this->deliveryReached($delivery, $stage));
        $all     = $reached->count() === $deliveries->count();

        return [
            'recorded'   => $all,
            'date'       => $all ? $reached->pluck($stage->column())->filter()->max() : null,
            'user_id'    => $all && $deliveries->count() === 1 ? $deliveries->first()->stageMarkUserId($stage->column()) : null,
            'deliveries' => ['reached' => $reached->count(), 'total' => $deliveries->count()],
        ];
    }

    private function deliveryReached(StockDelivery $delivery, Stage $stage): bool
    {
        return match ($stage) {
            Stage::SHIPPED  => in_array($delivery->state, self::SHIPPED_STATES),
            Stage::ARRIVED  => $delivery->arrived_at !== null || in_array($delivery->state, self::RECEIVED_STATES),
            Stage::RECEIVED => in_array($delivery->state, self::RECEIVED_STATES),
            default         => false,
        };
    }

    private function step(PurchaseOrder $purchaseOrder, Collection $deliveries, string $column): array
    {
        $step = ['key' => $column, 'label' => Stage::fieldLabels()[$column], 'date' => null, 'user_id' => null];

        if (in_array($column, self::WAREHOUSE_COLUMNS)) {
            return [...$step, 'date' => $this->warehouseDates($purchaseOrder)[$column]];
        }

        if (in_array($column, [...$purchaseOrder->stageMarkColumns(), 'online_at'])) {
            return [...$step, 'date' => $purchaseOrder->{$column}, 'user_id' => $purchaseOrder->stageMarkUserId($column)];
        }

        $dates = $deliveries->pluck($column);
        if ($dates->isEmpty() || $dates->contains(null)) {
            return $step;
        }

        return [
            ...$step,
            'date'    => $dates->max(),
            'user_id' => $deliveries->count() === 1 ? $deliveries->first()->stageMarkUserId($column) : null,
        ];
    }
}

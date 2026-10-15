<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 09:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder\UI;

use App\Enums\GoodsIn\StockDelivery\StockDeliveryStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStageEnum as Stage;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Where a purchase order stands in its seven stages. A delivery stage counts as reached only when every live
 * delivery of the order reached it, dated by the last of them, so the order shows its slowest delivery.
 */
class GetPurchaseOrderStages
{
    use AsObject;

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
        foreach (Stage::cases() as $stage) {
            $stages[$stage->value] = $stage->isDeliveryStage()
                ? $this->deliveryStage($stage, $deliveries)
                : [
                    'recorded'   => $purchaseOrder->{$stage->column()} !== null
                        || ($stage === Stage::SUBMITTED && $purchaseOrder->state !== PurchaseOrderStateEnum::IN_PROCESS)
                        || ($stage === Stage::DEPOSIT_PAID && $this->depositNotRequired($purchaseOrder)),
                    'date'       => $purchaseOrder->{$stage->column()},
                    'user_id'    => $purchaseOrder->stageMarkUserId($stage->column()),
                    'deliveries' => null,
                ];
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
        $stage  = Stage::MARKABLE_FIELDS[$field];
        $labels = Stage::labels();

        if ($field !== $stage->column()) {
            return [];
        }

        $skipped = [];
        foreach (Stage::cases() as $earlier) {
            if ($earlier->position() >= $stage->position()) {
                break;
            }
            if (!$stages[$earlier->value]['recorded']) {
                $skipped[] = $labels[$earlier->value];
            }
        }

        if ($stage->gate() && $purchaseOrder->{$stage->gate()} === null) {
            $skipped[] = Stage::fieldLabels()[$stage->gate()];
        }

        return $skipped;
    }

    public function depositNotRequired(PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->deposit_paid_at === null && $purchaseOrder->deposit_amount !== null && (float) $purchaseOrder->deposit_amount == 0;
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
        foreach (Stage::cases() as $stage) {
            foreach ($stage->steps() as $column) {
                $steps[$stage->value][] = $this->step($purchaseOrder, $deliveries, $column);
            }
        }

        $userIds = collect($stages)->pluck('user_id')
            ->merge(collect($steps)->flatten(1)->pluck('user_id'))
            ->filter()->unique();
        $names = User::whereIn('id', $userIds)->get(['id', 'contact_name', 'username'])
            ->mapWithKeys(fn (User $user) => [$user->id => $user->contact_name ?: $user->username]);

        $current = collect($stages)->filter(fn (array $stage) => $stage['reached'])->keys()->last();

        return collect(Stage::cases())->map(fn (Stage $stage) => [
            'key'         => $stage->value,
            'number'      => $stage->position() + 1,
            'label'       => Stage::labels()[$stage->value],
            'description' => Stage::descriptions()[$stage->value],
            'reached'     => $stages[$stage->value]['reached'],
            'current'     => $stage->value === $current,
            'date'        => $stages[$stage->value]['date'],
            'by'          => $names->get($stages[$stage->value]['user_id']),
            'deliveries'  => $stages[$stage->value]['deliveries'],
            'not_required' => $stage === Stage::DEPOSIT_PAID && $this->depositNotRequired($purchaseOrder),
            'steps'       => collect($steps[$stage->value] ?? [])->map(fn (array $step) => [
                'key'   => $step['key'],
                'label' => $step['label'],
                'date'  => $step['date'],
                'by'    => $names->get($step['user_id']),
            ])->all(),
            'marks'       => collect(Stage::MARKABLE_FIELDS)
                ->filter(fn (Stage $fieldStage, string $field) => $fieldStage === $stage && in_array($field, $markableFields)
                    && !($field === 'deposit_paid_at' && $this->depositNotRequired($purchaseOrder)))
                ->keys()
                ->map(fn (string $field) => [
                    'field'   => $field,
                    'label'   => $fieldLabels[$field],
                    'date'    => $purchaseOrder->{$field},
                    'skipped' => $this->skippedBy($purchaseOrder, $field, $stages),
                ])->values()->all(),
        ])->all();
    }

    private function liveDeliveries(PurchaseOrder $purchaseOrder): Collection
    {
        return $purchaseOrder->stockDeliveries()
            ->whereNotIn('stock_deliveries.state', [StockDeliveryStateEnum::CANCELLED, StockDeliveryStateEnum::NOT_RECEIVED])
            ->get();
    }

    private function deliveryStage(Stage $stage, Collection $deliveries): array
    {
        $reached = $deliveries->filter(fn (StockDelivery $delivery) => $this->deliveryReached($delivery, $stage));
        $all     = $deliveries->isNotEmpty() && $reached->count() === $deliveries->count();
        $dates   = $reached->pluck($stage->column());

        return [
            'recorded'   => $all,
            'date'       => $all && $dates->every(fn ($date) => $date !== null) ? $dates->max() : null,
            'user_id'    => $all && $deliveries->count() === 1 ? $deliveries->first()->stageMarkUserId($stage->column()) : null,
            'deliveries' => ['reached' => $reached->count(), 'total' => $deliveries->count()],
        ];
    }

    private function deliveryReached(StockDelivery $delivery, Stage $stage): bool
    {
        return match ($stage) {
            Stage::SHIPPED  => $delivery->dispatched_at !== null || in_array($delivery->state, self::SHIPPED_STATES),
            Stage::ARRIVED  => $delivery->arrived_at !== null || in_array($delivery->state, self::RECEIVED_STATES),
            Stage::RECEIVED => in_array($delivery->state, self::RECEIVED_STATES),
            default         => false,
        };
    }

    private function step(PurchaseOrder $purchaseOrder, Collection $deliveries, string $column): array
    {
        $step = ['key' => $column, 'label' => Stage::fieldLabels()[$column], 'date' => null, 'user_id' => null];

        if (in_array($column, $purchaseOrder->stageMarkColumns())) {
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

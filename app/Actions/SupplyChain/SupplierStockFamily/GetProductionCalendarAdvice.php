<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierStockFamily;

use App\Enums\SupplyChain\SupplierProduct\ProductionCalendarMonthEnum;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * When to order from a production calendar and a lead time. The factory produces right after the order, so an
 * order placed now runs into any month it can not produce within the next two months.
 */
class GetProductionCalendarAdvice
{
    use AsObject;

    public const string ORDER_BEFORE_STOP = 'order_before_stop';
    public const string GOOD_TIME = 'good_time';
    public const string CANNOT_PRODUCE = 'cannot_produce';
    public const string PEAK_OR_SLOW = 'peak_or_slow';
    public const string NOT_SET = 'not_set';

    /**
     * @param array<int|string, string|null> $calendar month number => ProductionCalendarMonthEnum value
     *
     * @return array{status: string, message: string|null, arrives_on: string|null}
     */
    public function handle(array $calendar, ?int $leadTimeDays, ?Carbon $today = null): array
    {
        $today     = ($today ?? now())->copy()->startOfDay();
        $markOf    = fn (Carbon $date) => $calendar[$date->month] ?? $calendar[(string) $date->month] ?? null;
        $thisMonth = $markOf($today);
        $arrivesOn = $leadTimeDays ? $today->copy()->addDays($leadTimeDays)->toDateString() : null;

        if (!array_filter($calendar)) {
            return ['status' => self::NOT_SET, 'message' => null, 'arrives_on' => $arrivesOn];
        }

        $stop = collect([1, 2])
            ->map(fn (int $months) => $today->copy()->startOfMonth()->addMonths($months))
            ->first(fn (Carbon $month) => $markOf($month) === ProductionCalendarMonthEnum::CANNOT_PRODUCE->value);

        if ($thisMonth !== ProductionCalendarMonthEnum::CANNOT_PRODUCE->value && $stop) {
            return [
                'status'     => self::ORDER_BEFORE_STOP,
                'message'    => __('Order now: the factory can not produce in :month.', ['month' => $stop->translatedFormat('F')]),
                'arrives_on' => $arrivesOn,
            ];
        }

        $nextGood = collect(range(1, 12))
            ->map(fn (int $months) => $today->copy()->startOfMonth()->addMonths($months))
            ->first(fn (Carbon $month) => $markOf($month) === ProductionCalendarMonthEnum::BEST_TO_ORDER->value);
        $nextGoodMonth = $nextGood?->translatedFormat('F');

        return match ($thisMonth) {
            ProductionCalendarMonthEnum::BEST_TO_ORDER->value => [
                'status'     => self::GOOD_TIME,
                'message'    => __('A good month to order.'),
                'arrives_on' => $arrivesOn,
            ],
            ProductionCalendarMonthEnum::CANNOT_PRODUCE->value => [
                'status'     => self::CANNOT_PRODUCE,
                'message'    => $nextGoodMonth ? __('The factory can not produce this month; next good month to order: :month.', ['month' => $nextGoodMonth]) : __('The factory can not produce this month.'),
                'arrives_on' => null,
            ],
            ProductionCalendarMonthEnum::PEAK_OR_SLOW->value => [
                'status'     => self::PEAK_OR_SLOW,
                'message'    => $nextGoodMonth ? __('A peak or slow month at the factory; next good month to order: :month.', ['month' => $nextGoodMonth]) : __('A peak or slow month at the factory.'),
                'arrives_on' => $arrivesOn,
            ],
            default => [
                'status'     => self::NOT_SET,
                'message'    => $nextGoodMonth ? __('Next good month to order: :month.', ['month' => $nextGoodMonth]) : null,
                'arrives_on' => $arrivesOn,
            ],
        };
    }
}

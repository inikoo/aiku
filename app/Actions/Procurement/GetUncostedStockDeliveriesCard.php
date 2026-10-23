<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 30 Sep 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement;

use App\Enums\GoodsIn\StockDelivery\StockDeliveryStateEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsObject;

class GetUncostedStockDeliveriesCard
{
    use AsObject;

    public const BOOKED_IN_GRACE_DAYS = 3;

    public const AGENT_LOOKBACK_DAYS = 90;

    public function handle(Organisation $organisation): ?array
    {
        $bookedInCutoff = now()->subDays(self::BOOKED_IN_GRACE_DAYS)->startOfDay();
        $agentSince     = now()->subDays(self::AGENT_LOOKBACK_DAYS)->startOfDay();

        $stale = StockDelivery::where('organisation_id', $organisation->id)
            ->where('state', StockDeliveryStateEnum::BOOKED_IN)
            ->where('booked_in_at', '<', $bookedInCutoff)
            ->selectRaw('count(*) as total, min(booked_in_at) as oldest')
            ->first();

        $agentNotCosted = StockDelivery::where('organisation_id', $organisation->id)
            ->where('parent_type', 'OrgAgent')
            ->where('state', StockDeliveryStateEnum::PLACED)
            ->where('is_costed', false)
            ->where('placed_at', '>=', $agentSince)
            ->count();

        $costsMissing = StockDelivery::where('organisation_id', $organisation->id)->withCostsMissing()->count();

        $estimated = StockDelivery::where('organisation_id', $organisation->id)
            ->where('is_costed', true)
            ->whereHas('costs', fn ($query) => $query->where('is_estimate', true))
            ->count();

        if (!$stale->total && !$agentNotCosted && !$costsMissing && !$estimated) {
            return null;
        }

        $total = StockDelivery::where('organisation_id', $organisation->id)
            ->where(fn ($query) => $query
                ->where(fn ($query) => $query->where('state', StockDeliveryStateEnum::BOOKED_IN)->where('booked_in_at', '<', $bookedInCutoff))
                ->orWhere(fn ($query) => $query->where('parent_type', 'OrgAgent')->where('state', StockDeliveryStateEnum::PLACED)->where('is_costed', false)->where('placed_at', '>=', $agentSince))
                ->orWhere(fn ($query) => $query->withCostsMissing())
                ->orWhere(fn ($query) => $query->where('is_costed', true)->whereHas('costs', fn ($query) => $query->where('is_estimate', true))))
            ->count();

        $staleRoute = $this->route($organisation, [
            'elements[state]'       => StockDeliveryStateEnum::BOOKED_IN->value,
            'between[booked_in_at]' => ($stale->oldest ? Carbon::parse($stale->oldest) : $bookedInCutoff)->format('Ymd').'-'.$bookedInCutoff->copy()->subDay()->format('Ymd'),
        ]);

        $agentRoute = $this->route($organisation, [
            'elements[state]'   => StockDeliveryStateEnum::PLACED->value,
            'elements[source]'  => 'OrgAgent',
            'elements[costing]' => 'not_costed',
            'between[placed_at]' => $agentSince->format('Ymd').'-'.now()->format('Ymd'),
        ]);

        return [
            'label'       => __('Not costed'),
            'description' => __('Stock deliveries waiting for costing'),
            'icon'        => 'fal fa-exclamation-triangle',
            'value'       => $total,
            'tone'        => 'amber',
            'attention'   => true,
            'route'       => match (true) {
                count(array_filter([$stale->total, $agentNotCosted, $costsMissing, $estimated])) > 1 => null,
                (bool) $costsMissing           => $this->route($organisation, ['elements[costing]' => 'costs_missing']),
                (bool) $estimated                => $this->route($organisation, ['elements[costing]' => 'estimated']),
                (bool) $stale->total             => $staleRoute,
                default                          => $agentRoute,
            },
            'metrics'     => [
                [
                    'label'       => __('Overdue'),
                    'description' => __('Booked in over :days days', ['days' => self::BOOKED_IN_GRACE_DAYS]),
                    'value' => $stale->total,
                    'route' => $staleRoute,
                ],
                [
                    'label'       => __('From agents'),
                    'description' => __('Last :days days', ['days' => self::AGENT_LOOKBACK_DAYS]),
                    'value' => $agentNotCosted,
                    'route' => $agentRoute,
                ],
                [
                    'label'       => __('Costs missing'),
                    'description' => __('Invoice, shipping or duty not entered'),
                    'value' => $costsMissing,
                    'route' => $this->route($organisation, ['elements[costing]' => 'costs_missing']),
                ],
                [
                    'label'       => __('Estimated'),
                    'description' => __('Waiting for invoice'),
                    'value' => $estimated,
                    'route' => $this->route($organisation, ['elements[costing]' => 'estimated']),
                ],
            ],
        ];
    }

    private function route(Organisation $organisation, array $query): array
    {
        return [
            'name'       => 'grp.org.procurement.stock_deliveries.index',
            'parameters' => [
                'organisation' => $organisation->slug,
                '_query'       => $query,
            ],
        ];
    }
}

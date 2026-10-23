<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Exports\Reports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Cost of goods sold is the cost of the stock picked in the period, in organisation currency.
 * A pick made against an aiku delivery note carries its shop; a pick imported from Aurora has
 * no delivery note, so its cost is split across shops by that org stock's invoiced revenue in
 * the same month, and that split is reported apart as allocated cost. Revenue is the invoiced
 * net of stock lines only (shipping, charges and services excluded).
 */
class CostOfGoodsSoldExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected int $organisationId,
        protected string $startDate,
        protected string $endDate,
    ) {
    }

    public function collection(): Collection
    {
        $bindings = [
            'organisation_id' => $this->organisationId,
            'start'           => $this->startDate,
            'end'             => $this->endDate,
        ];

        return collect(DB::connection('aiku_no_sticky')->select(
            "
            with picks as (
                select date_trunc('month', m.date)::date as month, m.org_stock_id, os.org_stock_family_id as family_id, dn.shop_id, -m.org_amount as cost
                from org_stock_movements m
                join org_stocks os on os.id = m.org_stock_id
                left join delivery_notes dn on m.parent_type = 'DeliveryNote' and dn.id = m.parent_id
                where m.organisation_id = :organisation_id and m.type = 'picked'
                  and m.date >= cast(:start as date) and m.date < cast(:end as date) + 1
            ),
            sales as (
                select date_trunc('month', it.date)::date as month, b.org_stock_id, b.org_stock_family_id as family_id, it.shop_id, sum(b.org_net_amount) as revenue
                from invoice_transaction_has_org_stocks b
                join invoice_transactions it on it.id = b.invoice_transaction_id
                where it.organisation_id = :organisation_id and it.deleted_at is null
                  and it.date >= cast(:start as date) and it.date < cast(:end as date) + 1
                group by 1, 2, 3, 4
            ),
            shares as (
                select month, org_stock_id, shop_id, revenue / sum(revenue) over (partition by month, org_stock_id) as share
                from sales
                where revenue > 0
            ),
            cogs as (
                select month, family_id, shop_id, cost, 0 as allocated_cost from picks where shop_id is not null
                union all
                select p.month, p.family_id, s.shop_id, p.cost * coalesce(s.share, 1), p.cost * coalesce(s.share, 1)
                from picks p
                left join shares s on s.month = p.month and s.org_stock_id = p.org_stock_id
                where p.shop_id is null
            ),
            c as (select month, family_id, shop_id, sum(cost) as cost, sum(allocated_cost) as allocated_cost from cogs group by 1, 2, 3),
            r as (select month, family_id, shop_id, sum(revenue) as revenue from sales group by 1, 2, 3)
            select coalesce(c.month, r.month) as month, shops.code as shop, f.code as family_code, f.name as family_name,
                   coalesce(r.revenue, 0) as revenue, coalesce(c.cost, 0) as cost, coalesce(c.allocated_cost, 0) as allocated_cost
            from c
            full join r on r.month = c.month and r.shop_id is not distinct from c.shop_id and r.family_id is not distinct from c.family_id
            left join shops on shops.id = coalesce(c.shop_id, r.shop_id)
            left join org_stock_families f on f.id = coalesce(c.family_id, r.family_id)
            order by 1, 2, 3
            ",
            $bindings
        ));
    }

    public function headings(): array
    {
        return ['Month', 'Shop', 'Family code', 'Family', 'Revenue', 'COGS', 'Gross profit', 'Margin %', 'COGS allocated by revenue share'];
    }

    public function map($row): array
    {
        $revenue = (float) $row->revenue;
        $cost    = (float) $row->cost;

        return [
            substr($row->month, 0, 7),
            $row->shop ?? 'No shop',
            $row->family_code ?? 'No family',
            $row->family_name ?? '',
            round($revenue, 2),
            round($cost, 2),
            round($revenue - $cost, 2),
            $revenue > 0 ? round(100 * ($revenue - $cost) / $revenue, 1) : null,
            round((float) $row->allocated_cost, 2),
        ];
    }
}

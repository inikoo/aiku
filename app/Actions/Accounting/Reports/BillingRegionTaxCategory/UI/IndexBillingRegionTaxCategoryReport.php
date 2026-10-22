<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Reports\BillingRegionTaxCategory\UI;

use App\Actions\Accounting\Reports\BillingRegionTaxCategory\WithBillingRegion;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithAccountingReportsAuthorisation;
use App\Actions\UI\Reports\IndexReports;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\Invoice;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexBillingRegionTaxCategoryReport extends OrgAction
{
    use WithAccountingReportsAuthorisation;
    use WithBillingRegion;

    /**
     * @var array{number_invoices: int, number_refunds: int, org_net_amount: float, org_tax_amount: float, org_total_amount: float}
     */
    private array $totals;

    public function handle(Organisation $organisation): LengthAwarePaginator
    {
        [$billingRegionSql, $billingRegionBindings] = $this->billingRegionSql($organisation);

        $query = QueryBuilder::for(Invoice::class)
            ->where('invoices.organisation_id', $organisation->id)
            ->where('invoices.in_process', false)
            ->leftJoin('tax_categories', 'tax_categories.id', '=', 'invoices.tax_category_id');
        $this->joinBillingCountry($query);
        $query->withBetweenDates(['date']);

        $totals       = (clone $query)->toBase()->selectRaw($this->aggregatesSql())->first();
        $this->totals = [
            'number_invoices'  => (int) $totals->number_invoices,
            'number_refunds'   => (int) $totals->number_refunds,
            'org_net_amount'   => (float) $totals->org_net_amount,
            'org_tax_amount'   => (float) $totals->org_tax_amount,
            'org_total_amount' => (float) $totals->org_total_amount,
        ];

        return $query
            ->selectRaw("$billingRegionSql as billing_region", $billingRegionBindings)
            ->addSelect(['tax_categories.id as tax_category_id', 'tax_categories.label as tax_code', 'tax_categories.name as tax_category_name'])
            ->selectRaw($this->aggregatesSql())
            ->groupBy('billing_region', 'tax_categories.id', 'tax_categories.label', 'tax_categories.name')
            ->defaultSort('billing_region')
            ->allowedSorts(['billing_region', 'tax_code', 'number_invoices', 'number_refunds', 'org_net_amount', 'org_tax_amount', 'org_total_amount'])
            ->withPaginator(null, tableName: request()->route()?->getName())
            ->withQueryString();
    }

    private function aggregatesSql(): string
    {
        $invoice = InvoiceTypeEnum::INVOICE->value;

        return "count(*) filter (where invoices.type = '$invoice') as number_invoices,
            count(*) filter (where invoices.type != '$invoice') as number_refunds,
            coalesce(sum(invoices.org_net_amount), 0) as org_net_amount,
            coalesce(sum(invoices.tax_amount * invoices.org_exchange), 0) as org_tax_amount,
            coalesce(sum(invoices.total_amount * invoices.org_exchange), 0) as org_total_amount";
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $currencyCode = $this->organisation->currency->code;

            $table->betweenDates(['date'])
                ->withLabelRecord([__('row'), __('rows')])
                ->withEmptyState(['title' => __('No invoices in these dates')])
                ->withFooterRows(['data' => [[
                    'billing_region'   => __('Total'),
                    'number_invoices'  => $this->totals['number_invoices'],
                    'number_refunds'   => $this->totals['number_refunds'],
                    'org_net_amount'   => $this->totals['org_net_amount'],
                    'org_tax_amount'   => $this->totals['org_tax_amount'],
                    'org_total_amount' => $this->totals['org_total_amount'],
                ]]]);

            $table->column(key: 'billing_region', label: __('Billing region'), canBeHidden: false, sortable: true);
            $table->column(key: 'tax_code', label: __('Tax code'), canBeHidden: false, sortable: true);
            $table->column(key: 'number_invoices', label: __('Invoices'), canBeHidden: false, sortable: true, type: 'number');
            $table->column(key: 'number_refunds', label: __('Refunds'), canBeHidden: false, sortable: true, type: 'number');
            $table->column(key: 'org_net_amount', label: __('Net')." ($currencyCode)", canBeHidden: false, sortable: true, type: 'number');
            $table->column(key: 'org_tax_amount', label: __('Tax')." ($currencyCode)", canBeHidden: false, sortable: true, type: 'number');
            $table->column(key: 'org_total_amount', label: __('Total')." ($currencyCode)", canBeHidden: false, sortable: true, type: 'number');
        };
    }

    public function htmlResponse(LengthAwarePaginator $rows, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Reports/BillingRegionTaxCategoryReport',
            [
                'breadcrumbs'           => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),
                'title'                 => __('Billing region & tax code'),
                'pageHead'              => [
                    'title' => __('Billing region & tax code'),
                    'icon'  => [
                        'icon'  => ['fal', 'fa-globe-europe'],
                        'title' => __('Invoices and refunds by billing region and tax code'),
                    ],
                ],
                'data'                  => JsonResource::collection($rows),
                'currency_code'         => $this->organisation->currency->code,
                'billing_region_labels' => $this->billingRegionLabels($this->organisation),
            ]
        )->table($this->tableStructure());
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexReports::make()->getBreadcrumbs($routeName, $routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'label' => __('Billing region & tax code'),
                        'route' => [
                            'name'       => 'grp.org.reports.billing-region-tax-code',
                            'parameters' => ['organisation' => $routeParameters['organisation']],
                        ],
                    ],
                ],
            ]
        );
    }
}

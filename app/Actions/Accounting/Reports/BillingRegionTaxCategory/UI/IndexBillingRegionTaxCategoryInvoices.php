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
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\Invoice;
use App\Models\Helpers\TaxCategory;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexBillingRegionTaxCategoryInvoices extends OrgAction
{
    use WithAccountingReportsAuthorisation;
    use WithBillingRegion;

    private string $billingRegion;
    private TaxCategory $taxCategory;

    public function handle(Organisation $organisation, string $billingRegion, TaxCategory $taxCategory): LengthAwarePaginator
    {
        [$billingRegionSql, $billingRegionBindings] = $this->billingRegionSql($organisation);

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereWith('invoices.reference', $value)->orWhereWith('invoices.customer_name', $value);
            });
        });

        $query = QueryBuilder::for(Invoice::class)
            ->where('invoices.organisation_id', $organisation->id)
            ->where('invoices.in_process', false)
            ->where('invoices.tax_category_id', $taxCategory->id);
        $this->joinBillingCountry($query);

        return $query->whereRaw("($billingRegionSql) = ?", [...$billingRegionBindings, $billingRegion])
            ->select([
                'invoices.id',
                'invoices.slug',
                'invoices.reference',
                'invoices.type',
                'invoices.date',
                'invoices.customer_name',
                'invoices.org_net_amount',
                'billing_countries.name as billing_country',
            ])
            ->selectRaw('invoices.tax_amount * invoices.org_exchange as org_tax_amount')
            ->selectRaw('invoices.total_amount * invoices.org_exchange as org_total_amount')
            ->defaultSort('-date')
            ->allowedSorts(['date', 'reference', 'type', 'customer_name', 'billing_country', 'org_net_amount', 'org_tax_amount', 'org_total_amount'])
            ->allowedFilters([$globalSearch])
            ->withBetweenDates(['date'])
            ->withPaginator(null, tableName: request()->route()?->getName())
            ->withQueryString();
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $currencyCode = $this->organisation->currency->code;

            $table->betweenDates(['date'])
                ->withGlobalSearch()
                ->withLabelRecord([__('invoice'), __('invoices')])
                ->withEmptyState(['title' => __('No invoices in these dates')]);

            $table->column(key: 'reference', label: __('Reference'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'type', label: __('Type'), canBeHidden: false, sortable: true);
            $table->column(key: 'date', label: __('Date'), canBeHidden: false, sortable: true);
            $table->column(key: 'customer_name', label: __('Customer'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'billing_country', label: __('Billing country'), canBeHidden: false, sortable: true);
            $table->column(key: 'org_net_amount', label: __('Net')." ($currencyCode)", canBeHidden: false, sortable: true, type: 'number');
            $table->column(key: 'org_tax_amount', label: __('Tax')." ($currencyCode)", canBeHidden: false, sortable: true, type: 'number');
            $table->column(key: 'org_total_amount', label: __('Total')." ($currencyCode)", canBeHidden: false, sortable: true, type: 'number');
        };
    }

    public function htmlResponse(LengthAwarePaginator $invoices, ActionRequest $request): Response
    {
        $billingRegionLabel = Arr::get($this->billingRegionLabels($this->organisation), $this->billingRegion, $this->billingRegion);

        return Inertia::render(
            'Org/Reports/BillingRegionTaxCategoryInvoices',
            [
                'breadcrumbs'   => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),
                'title'         => __('Billing region & tax code'),
                'pageHead'      => [
                    'title'      => $billingRegionLabel.' · '.$this->taxCategory->label,
                    'model'      => __('Billing region & tax code'),
                    'afterTitle' => ['label' => $this->taxCategory->name],
                    'icon'       => [
                        'icon'  => ['fal', 'fa-globe-europe'],
                        'title' => __('Billing region & tax code'),
                    ],
                ],
                'data'          => JsonResource::collection($invoices),
                'currency_code' => $this->organisation->currency->code,
            ]
        )->table($this->tableStructure());
    }

    public function asController(Organisation $organisation, string $billingRegion, TaxCategory $taxCategory, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        abort_unless(array_key_exists($billingRegion, $this->billingRegionLabels($organisation)), 404);
        $this->billingRegion = $billingRegion;
        $this->taxCategory   = $taxCategory;

        return $this->handle($organisation, $billingRegion, $taxCategory);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexBillingRegionTaxCategoryReport::make()->getBreadcrumbs($routeName, $routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'label' => Arr::get($this->billingRegionLabels($this->organisation), $this->billingRegion, $this->billingRegion).' · '.$this->taxCategory->label,
                        'route' => [
                            'name'       => $routeName,
                            'parameters' => $routeParameters,
                        ],
                    ],
                ],
            ]
        );
    }
}

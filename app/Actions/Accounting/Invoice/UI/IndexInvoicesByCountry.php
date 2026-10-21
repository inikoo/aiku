<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 23:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\UI;

use App\Actions\Accounting\Invoice\WithInvoiceDeliveryCountry;
use App\Actions\Accounting\Invoice\WithInvoicesSubNavigation;
use App\Actions\Accounting\InvoiceCategory\UI\ShowInvoiceCategory;
use App\Actions\Accounting\InvoiceCategory\WithInvoiceCategorySubNavigation;
use App\Actions\Accounting\UI\ShowAccountingDashboard;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithInvoiceCategoryListAuthorisation;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceCategory;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexInvoicesByCountry extends OrgAction
{
    use WithInvoiceCategorySubNavigation;
    use WithInvoicesSubNavigation;
    use WithInvoiceCategoryListAuthorisation;
    use WithInvoiceDeliveryCountry;

    private Organisation|InvoiceCategory $parent;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof InvoiceCategory) {
            return $this->authorizeInvoiceCategoryList($request->user(), $this->organisation);
        }

        return $request->user()->authTo("accounting.{$this->organisation->id}.view");
    }

    public function handle(Organisation|InvoiceCategory $parent): LengthAwarePaginator
    {
        $query = QueryBuilder::for(Invoice::class)
            ->where('invoices.type', InvoiceTypeEnum::INVOICE)
            ->where('invoices.in_process', false);

        if ($parent instanceof InvoiceCategory) {
            $query->where('invoices.invoice_category_id', $parent->id);
            if ($this->invoiceCategoryViewableShopIds !== null) {
                $query->whereIn('invoices.shop_id', $this->invoiceCategoryViewableShopIds);
            }
        } else {
            $query->where('invoices.organisation_id', $parent->id);
        }

        $this->joinInvoiceDeliveryCountry($query);

        return $query
            ->select([
                'delivery_countries.code as country_code',
                'delivery_countries.name as country_name',
                DB::raw('count(*) as number_invoices'),
                DB::raw('sum(invoices.org_net_amount) as org_net_amount'),
            ])
            ->groupBy('delivery_countries.code', 'delivery_countries.name')
            ->defaultSort('-number_invoices')
            ->allowedSorts(['country_name', 'number_invoices', 'org_net_amount'])
            ->withBetweenDates(['date'])
            ->withPaginator(null, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table->betweenDates(['date'])
                ->withLabelRecord([__('country'), __('countries')])
                ->withEmptyState(['title' => __('No invoices in these dates')]);

            $table->column(key: 'country_name', label: __('Delivered to'), canBeHidden: false, sortable: true);
            $table->column(key: 'number_invoices', label: __('Invoices'), canBeHidden: false, sortable: true, type: 'number');
            $table->column(key: 'org_net_amount', label: __('Net').' ('.$this->organisation->currency->code.')', canBeHidden: false, sortable: true, type: 'number')
                ->defaultSort('-number_invoices');
        };
    }

    public function htmlResponse(LengthAwarePaginator $countries, ActionRequest $request): Response
    {
        $isCategory = $this->parent instanceof InvoiceCategory;

        return Inertia::render(
            'Org/Accounting/InvoicesByCountry',
            [
                'breadcrumbs'         => $this->getBreadcrumbs($this->parent, $request->route()->originalParameters()),
                'title'               => __('By country'),
                'pageHead'            => [
                    'title'         => $isCategory ? $this->parent->name : __('Invoices'),
                    'model'         => __('By country'),
                    'icon'          => [
                        'icon'  => ['fal', 'fa-globe'],
                        'title' => __('Invoices by the country they were delivered to'),
                    ],
                    'subNavigation' => $isCategory ? $this->getInvoiceCategoryNavigation($this->parent) : $this->getInvoicesNavigation($this->parent),
                ],
                'data'                => JsonResource::collection($countries),
                'currency_code'       => $this->organisation->currency->code,
                'countryInvoicesRoute' => [
                    'name'       => $isCategory
                        ? 'grp.org.accounting.invoice-categories.show.countries.show.invoices.index'
                        : 'grp.org.accounting.invoices_by_country.show.invoices.index',
                    'parameters' => $request->route()->originalParameters(),
                ],
            ]
        )->table($this->tableStructure());
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inInvoiceCategory(Organisation $organisation, InvoiceCategory $invoiceCategory, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $invoiceCategory;
        $this->initialisation($invoiceCategory->organisation, $request);

        return $this->handle($invoiceCategory);
    }

    public function getBreadcrumbs(Organisation|InvoiceCategory $parent, array $routeParameters): array
    {
        if ($parent instanceof InvoiceCategory) {
            $routeName = 'grp.org.accounting.invoice-categories.show.countries.index';
            $parentBreadcrumbs = ShowInvoiceCategory::make()->getBreadcrumbs($parent, $routeName, $routeParameters);
        } else {
            $routeName = 'grp.org.accounting.invoices_by_country.index';
            $parentBreadcrumbs = ShowAccountingDashboard::make()->getBreadcrumbs('grp.org.accounting.dashboard', $routeParameters);
        }

        return array_merge(
            $parentBreadcrumbs,
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => $routeName,
                            'parameters' => $routeParameters,
                        ],
                        'label' => __('Invoices by country'),
                    ],
                ],
            ]
        );
    }
}

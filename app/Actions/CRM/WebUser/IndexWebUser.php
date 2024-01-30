<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:25:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\OrgAction;
use App\Actions\Web\Website\UI\ShowWebsite;
use App\Http\Resources\CRM\WebUserResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\SysAdmin\InertiaTableWebUserResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Customer;
use App\Models\SysAdmin\Organisation;
use App\Models\Market\Shop;
use App\Models\SysAdmin\WebUser;
use App\Models\Web\Website;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexWebUser extends OrgAction
{
    private Shop|Organisation|Customer|Website $parent;


    public function handle($parent, $prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('webusers.email', 'LIKE', "%$value%");
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(WebUser::class);
        foreach ($this->elementGroups as $key => $elementGroup) {

            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        return $queryBuilder
            ->defaultSort('customers.email')
            ->select(['email', 'web_users.id'])
            //->leftJoin('shops', 'shops.id', 'shop_id')
            ->when($parent, function ($query) use ($parent) {
                switch (class_basename($parent)) {
                    case 'Customer':
                        $query->where('web_users.customer_id', $parent->id);
                        break;
                    case 'Website':
                        $query->where('web_users.website_id', $parent->id);
                        break;
                }
            })
            ->allowedSorts(['email'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root')                 or
                $request->user()->hasPermissionTo('websites.view') or
                $request->user()->hasPermissionTo("crm.{$this->shop->id}.view")
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return CustomerResource::collection($this->handle($this->parent));
    }


    public function htmlResponse(LengthAwarePaginator $webusers, ActionRequest $request): Response
    {
        return Inertia::render(
            'Web/WebUsers',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters(),
                ),
                'title'       => __('web users'),
                'pageHead'    => [
                    'title' => __('web users'),
                ],
                'customers'   => InertiaTableWebUserResource::collection($webusers),
                'data'        => WebUserResource::collection($webusers),

            ]
        )->table($this->tableStructure());
    }

    public function tableStructure(?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix) {
            if($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->column(key: 'email', label: __('email'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('email');
        };
    }


    public function inOrganisation(Request $request): LengthAwarePaginator
    {
        $this->fillFromRequest($request);
        $this->parent    = app('currentTenant');

        return $this->handle($this->parent);
    }
    public function inWebsite(Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->parent = $website;
        return $this->handle(parent:  $website);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inCustomerInShop(Shop $shop, Customer $customer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $customer;
        $this->initialisation($request);
        return $this->handle(parent: $customer);
    }



    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('web users')
                    ]
                ],
            ];
        };
        return match ($routeName) {
            'customers.show.web-users.index' => array_merge(
                (new ShowCustomer())->getBreadcrumbs('customers.show', $routeParameters),
                $routeParameters
            ),
            'shops.show.customers.show.web-users.index' =>
            array_merge(
                (new ShowCustomer())->getBreadcrumbs('shops.show.customers.show', $routeParameters),
                $headCrumb($routeParameters)
            ),
            'grp.org.shops.show.web.websites.show.web-users.index' =>
            array_merge(
                (new ShowWebsite())->getBreadcrumbs(
                    routeName: $routeName,
                    routeParameters: $routeParameters
                ),
                $headCrumb(
                    $routeParameters
                )
            ),
            default => []
        };
    }
}

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
use App\Http\Resources\CRM\WebUsersResource;
use App\Http\Resources\Sales\CustomerResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Customer;
use App\Models\SysAdmin\Organisation;
use App\Models\Market\Shop;
use App\Models\SysAdmin\WebUser;
use App\Models\Web\Website;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexWebUsers extends OrgAction
{
    private Shop|Organisation|Customer|Website $parent;


    public function handle(Shop|Organisation|Customer|Website $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('webusers.username', $value);
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(WebUser::class);

        if ($parent instanceof Customer) {
            $queryBuilder->where('customer_id', $parent->id);
        } elseif ($parent instanceof Website) {
            $queryBuilder->where('website_id', $parent->id);
        } elseif ($parent instanceof Organisation) {
            $queryBuilder->where('organisation_id', $parent->id);
        } else {
            $queryBuilder->where('shop_id', $parent->id);
        }


        return $queryBuilder
            ->defaultSort('customers.username')
            ->select(['username', 'web_users.id', 'email', 'slug'])
            ->allowedSorts(['email', 'username'])
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
                'customers'   => WebUsersResource::collection($webusers),
                'data'        => WebUserResource::collection($webusers),

            ]
        )->table($this->tableStructure($this->parent));
    }

    public function tableStructure(Shop|Organisation|Customer|Website $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->column(key: 'username', label: __('username'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'email', label: __('email'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('username');
        };
    }


    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($this->parent);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWebsite(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromShop($shop, $request);
        $this->parent = $website;

        return $this->handle(parent: $website);
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

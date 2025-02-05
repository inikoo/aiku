<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 23-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\CRM\WebUser;

use App\Actions\OrgAction;
use App\InertiaTable\InertiaTable;
use App\Models\Analytics\WebUserRequest;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;

class IndexWebUserRequests extends OrgAction
{
    use WithAuthorizeWebUserScope;

    private Group|Shop|Organisation|Customer|FulfilmentCustomer|Website $parent;


    public function handle(Group|Shop|Organisation|Customer|FulfilmentCustomer|Website $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('web_users.username', $value);
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(WebUserRequest::class);
        if ($parent instanceof Website) {
            $queryBuilder->where('web_user_requests.website_id', $parent->id);
        } elseif ($parent instanceof FulfilmentCustomer) {
            $queryBuilder->where('web_user_requests.website_id', $parent->customer->shop->website->id);
        } elseif ($parent instanceof Customer) {
            $queryBuilder->where('web_user_requests.website_id', $parent->shop->website->id);
        } elseif ($parent instanceof Shop) {
            $queryBuilder->where('web_user_requests.website_id', $parent->website->id);
        } elseif ($parent instanceof Organisation) {
            $queryBuilder->whereExists(function ($query) use ($parent) {
                $query->select('id')
                    ->from('web_users')
                    ->whereColumn('web_users.id', 'web_user_requests.web_user_id')
                    ->whereIn('web_users.id', $parent->webUsers->pluck('id'));
            });
        } elseif ($parent instanceof Group) {
            $queryBuilder->where('web_user_requests.group_id', $parent->id);
        }

        $queryBuilder->leftJoin('web_users', 'web_users.id', '=', 'web_user_requests.web_user_id');


        return $queryBuilder
            ->defaultSort('web_users.username')
            ->select([
                'web_users.username',
                'web_users.id',
                'web_user_requests.*'
                ])
            ->allowedSorts(['username', 'ip_address', 'date'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(Group|Shop|Organisation|Customer|FulfilmentCustomer|Website $parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->column(key: 'username', label: __('username'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'ip_address', label: __('ip address'), canBeHidden: false, sortable: true, searchable: false)
                ->column(key: 'url', label: __('url'), canBeHidden: false, sortable: false, searchable: false)
                ->column(key: 'user_agent', label: __('user agent'), canBeHidden: false, sortable: false, searchable: false)
                ->column(key: 'location', label: __('location'), canBeHidden: false, sortable: false, searchable: false)
                ->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: false)
                ->defaultSort('-date');
        };
    }
}

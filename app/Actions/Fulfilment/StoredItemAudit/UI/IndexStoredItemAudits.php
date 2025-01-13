<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAudit\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemAudit;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexStoredItemAudits extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;
    use WithFulfilmentCustomerSubNavigation;

    public function asController(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilmentCustomer);
    }

    public function handle(FulfilmentCustomer|Fulfilment $parent, $prefix = null): LengthAwarePaginator
    {

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereWith('stored_item_audits.reference', $value);
            });
        });


        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(StoredItemAudit::class);


        if ($parent instanceof FulfilmentCustomer) {
            $query->where('fulfilment_customer_id', $parent->id);
        } else {
            $query->where('fulfilment_id', $parent->id);
        }


        $query->defaultSort('stored_item_audits.date');


        return $query->allowedSorts(['state', 'reference','date','amount','tax_amount','total_amount','number_pallets','number_stored_items','number_added_stored_items','number_edited_stored_items','number_removed_stored_items'])
            ->allowedFilters([$globalSearch,  'reference'])
            ->withPaginator($prefix)
            ->withQueryString();

    }

    public function htmlResponse(StoredItemAudit $storedItemAudit, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/StoredItemAudits',
            [
                'title'       => __('stored item audit'),
                'breadcrumbs' => $this->getBreadcrumbs($storedItemAudit),
                'pageHead'    => [
                    'icon'    =>
                        [
                            'icon'  => ['fal', 'fa-narwhal'],
                            'title' => __('stored item audit')
                        ],
                    'model'   => 'stored item',
                    'title'   => $storedItemAudit->slug,

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => [],
                ],


            ]
        );
    }



    public function getBreadcrumbs(StoredItemAudit $storedItemAudit, $suffix = null): array
    {
        return array_merge(
            ShowFulfilmentCustomer::make()->getBreadcrumbs(request()->route()->originalParameters()),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-items.index',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => __("customer's sKUs")
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-items.show',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => $storedItemAudit->slug,
                        ],
                    ],
                    'suffix'         => $suffix,
                ],
            ]
        );
    }
}

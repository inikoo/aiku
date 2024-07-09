<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\Catalogue\HasRentalAgreement;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\UI\GetFulfilmentCustomerShowcase;
use App\Actions\Fulfilment\RentalAgreementClause\UI\IndexRentalAgreementClauses;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItems;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\Traits\WithWebUserMeta;
use App\Enums\Fulfilment\FulfilmentCustomer\FulfilmentCustomerStatus;
use App\Enums\UI\Fulfilment\FulfilmentCustomerTabsEnum;
use App\Http\Resources\CRM\CustomersResource;
use App\Http\Resources\Fulfilment\RentalAgreementClausesResource;
use App\Http\Resources\History\HistoryResource;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowFulfilmentCustomer extends OrgAction
{
    use WithWebUserMeta;
    use HasRentalAgreement;
    use WithFulfilmentCustomerSubNavigation;

    public function handle(FulfilmentCustomer $fulfilmentCustomer): FulfilmentCustomer
    {
        return $fulfilmentCustomer;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
    }

    public function asController(
        Organisation $organisation,
        Fulfilment $fulfilment,
        FulfilmentCustomer $fulfilmentCustomer,
        ActionRequest $request
    ): FulfilmentCustomer {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(FulfilmentCustomerTabsEnum::values());

        return $this->handle($fulfilmentCustomer);
    }

    public function htmlResponse(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): Response
    {
        $webUsersMeta = $this->getWebUserMeta($fulfilmentCustomer->customer, $request);


        $navigation = FulfilmentCustomerTabsEnum::navigation();

        if (!$fulfilmentCustomer->rentalAgreement) {
            unset($navigation[FulfilmentCustomerTabsEnum::AGREED_PRICES->value]);
        }

        /*
                if (!$fulfilmentCustomer->pallets_storage) {
                    unset($navigation[FulfilmentCustomerTabsEnum::PALLETS->value]);
                    unset($navigation[FulfilmentCustomerTabsEnum::PALLET_DELIVERIES->value]);
                    unset($navigation[FulfilmentCustomerTabsEnum::PALLET_RETURNS->value]);
                }
                if (!$fulfilmentCustomer->items_storage) {
                    unset($navigation[FulfilmentCustomerTabsEnum::STORED_ITEM_RETURNS->value]);
                    unset($navigation[FulfilmentCustomerTabsEnum::STORED_ITEMS->value]);
                }
        */
        // todo
        //if (!$fulfilmentCustomer->dropshipping) {
        //}
        /*
                if(!$fulfilmentCustomer->rentalAgreement || ($fulfilmentCustomer->rentalAgreement->state != RentalAgreementStateEnum::ACTIVE)) {
                    unset($navigation[FulfilmentCustomerTabsEnum::PALLETS->value]);
                    unset($navigation[FulfilmentCustomerTabsEnum::INVOICES->value]);
                    unset($navigation[FulfilmentCustomerTabsEnum::RECURRING_BILLS->value]);
                    unset($navigation[FulfilmentCustomerTabsEnum::PALLET_RETURNS->value]);
                }
        */

        $additionalActions = [
                    [
                        'type'     => 'button',
                        'style'    => 'create',
                        'tooltip'  => $fulfilmentCustomer->status == FulfilmentCustomerStatus::NO_RENTAL_AGREEMENT ? __('Rental Agreement is not exist') : __('Create a pallet Delivery'),
                        'label'    => __('Delivery'),
                        'disabled' => $fulfilmentCustomer->status == FulfilmentCustomerStatus::NO_RENTAL_AGREEMENT,
                        'route'    => [
                            'method'     => 'post',
                            'name'       => 'grp.models.fulfilment-customer.pallet-delivery.store',
                            'parameters' => [
                                'fulfilmentCustomer' => $fulfilmentCustomer->id
                            ]
                        ]
                    ],
        ];


        if($fulfilmentCustomer->number_pallets_status_storing > 0) {
            $additionalActions[] =
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('Create Return'),
                            'label'   => __('Return'),
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'grp.models.fulfilment-customer.pallet-return.store',
                                'parameters' => [
                                    'fulfilmentCustomer' => $fulfilmentCustomer->id
                                ]
                            ]
                        ];

        }

        return Inertia::render(
            'Org/Fulfilment/FulfilmentCustomer',
            [
                'title'       => __('customer'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation' => [
                    'previous' => $this->getPrevious($fulfilmentCustomer, $request),
                    'next'     => $this->getNext($fulfilmentCustomer, $request),
                ],
                'pageHead' => [
                    'icon' => [
                        'title' => __('customer'),
                        'icon'  => 'fal fa-user',

                    ],
                    'model'        => __('Customer'),
                    'subNavigation'=> $this->getFulfilmentCustomerSubNavigation($fulfilmentCustomer, $request),
                    'meta'         => array_filter([
                        $webUsersMeta,
                    ]),
                    'title' => $fulfilmentCustomer->customer->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                    'actions' => [
                        [
                            'type'    => 'button',
                            'style'   => 'edit',
                            'tooltip' => __('Edit Customer'),
                            'label'   => __('Edit Customer'),
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.edit',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ],
                        [
                            'type'      => 'buttonGroup',
                            'button'    => $additionalActions
                        ]
                    ]
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => $navigation
                ],

                FulfilmentCustomerTabsEnum::SHOWCASE->value => $this->tab == FulfilmentCustomerTabsEnum::SHOWCASE->value ?
                    fn () => GetFulfilmentCustomerShowcase::run($fulfilmentCustomer, $request)
                    : Inertia::lazy(fn () => GetFulfilmentCustomerShowcase::run($fulfilmentCustomer, $request)),

                FulfilmentCustomerTabsEnum::AGREED_PRICES->value => $this->tab == FulfilmentCustomerTabsEnum::AGREED_PRICES->value ?
                    fn () => RentalAgreementClausesResource::collection(IndexRentalAgreementClauses::run($fulfilmentCustomer, FulfilmentCustomerTabsEnum::AGREED_PRICES->value))
                    : Inertia::lazy(fn () => RentalAgreementClausesResource::collection(IndexRentalAgreementClauses::run($fulfilmentCustomer, FulfilmentCustomerTabsEnum::AGREED_PRICES->value))),

                FulfilmentCustomerTabsEnum::WEBHOOK->value => $this->tab == FulfilmentCustomerTabsEnum::WEBHOOK->value ?
                    fn () => GetFulfilmentCustomerShowcase::run($fulfilmentCustomer, $request)
                    : Inertia::lazy(fn () => GetFulfilmentCustomerShowcase::run($fulfilmentCustomer, $request)),

                FulfilmentCustomerTabsEnum::HISTORY->value => $this->tab == FulfilmentCustomerTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($fulfilmentCustomer->customer))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($fulfilmentCustomer->customer))),


            ]
        )
            ->table(IndexStoredItems::make()->tableStructure($fulfilmentCustomer->storedItems))
            ->table(IndexRentalAgreementClauses::make()->tableStructure(prefix: FulfilmentCustomerTabsEnum::AGREED_PRICES->value))
            ->table(IndexHistory::make()->tableStructure(prefix: FulfilmentCustomerTabsEnum::HISTORY->value));
    }


    public function jsonResponse(Customer $fulfilmentCustomer): CustomersResource
    {
        return new CustomersResource($fulfilmentCustomer);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        $headCrumb = function (FulfilmentCustomer $fulfilmentCustomer, array $routeParameters, string $suffix = '') {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Customers')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $fulfilmentCustomer->customer->reference,
                        ],

                    ],
                    'suffix' => $suffix

                ],
            ];
        };

        if (Arr::get($routeParameters, 'pallet')) {
            $pallet             = Pallet::where('slug', $routeParameters['pallet'])->first();
            $fulfilmentCustomer = $pallet->fulfilmentCustomer->slug;
        } else {
            $fulfilmentCustomer = $routeParameters['fulfilmentCustomer'];
        }

        $fulfilmentCustomer = FulfilmentCustomer::where('slug', $fulfilmentCustomer)->first();
        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs(
                Arr::only($routeParameters, ['organisation', 'fulfilment'])
            ),
            $headCrumb(
                $fulfilmentCustomer,
                [

                    'index' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.index',
                        'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment'])
                    ],
                    'model' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show',
                        'parameters' => Arr::only(
                            $routeParameters,
                            ['organisation', 'fulfilment', 'fulfilmentCustomer']
                        )
                    ]
                ]
            )
        );
    }

    public function getPrevious(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): ?array
    {
        $previous = FulfilmentCustomer::where('slug', '<', $fulfilmentCustomer->slug)
            ->where('fulfilment_customers.fulfilment_id', $fulfilmentCustomer->fulfilment_id)
            ->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): ?array
    {
        $next = FulfilmentCustomer::where('slug', '>', $fulfilmentCustomer->slug)
            ->where('fulfilment_customers.fulfilment_id', $fulfilmentCustomer->fulfilment_id)
            ->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?FulfilmentCustomer $fulfilmentCustomer, string $routeName): ?array
    {
        if (!$fulfilmentCustomer) {
            return null;
        }

        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show' => [
                'label' => $fulfilmentCustomer->customer->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'       => $fulfilmentCustomer->organisation->slug,
                        'fulfilment'         => $this->fulfilment->slug,
                        'fulfilmentCustomer' => $fulfilmentCustomer->slug
                    ]

                ]
            ],
        };
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreement\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Http\Resources\Catalogue\ProductClausesResource;
use App\Http\Resources\Catalogue\RentalClausesResource;
use App\Http\Resources\Catalogue\ServiceClausesResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\RentalAgreement;
use App\Models\SysAdmin\Organisation;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditRentalAgreement extends OrgAction
{
    /**
     * @throws Exception
     */
    public function handle(RentalAgreement $rentalAgreement, ActionRequest $request): Response
    {
        $rentals = [];
        foreach ($rentalAgreement->fulfilmentCustomer->rentalAgreementClauses as $clause) {
            $price       = $clause->asset->price;
            $agreedPrice = $clause->agreed_price;

            $rentals[] = [
                'asset_id'       => $clause->asset_id,
                'agreed_price'     => $agreedPrice,
                'price'            => $price,
                'discount'         => ($price - $agreedPrice) / $agreedPrice * 100
            ];
        }
        // dd($rentalAgreement->clauses->pluck('agreed_price'));

        return Inertia::render(
            'EditModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'    => __('edit rental agreement'),
                'pageHead' => [
                    'title' => __('edit rental agreement')
                ],
                'formData' => [
                    'fullLayout' => true,
                    'blueprint'  =>
                        [
                            [
                                'title'  => __(''),
                                'fields' => [
                                    'billing_cycle' => [
                                        'type'       => 'select',
                                        'label'      => __('billing cycle'),
                                        'required'   => true,
                                        'options'    => Options::forEnum(RentalAgreementBillingCycleEnum::class),
                                        'value'      => $rentalAgreement->billing_cycle
                                    ],
                                    'pallets_limit' => [
                                        'type'        => 'input',
                                        'label'       => __('pallets limit'),
                                        'placeholder' => '0',
                                        'required'    => false,
                                        'value'       => $rentalAgreement->pallets_limit
                                    ],
                                    'rental' => [
                                        'type'             => 'rental',
                                        'label'            => __('Rental'),
                                        'required'         => false,
                                        'full'             => true,
                                        'rentals'          => RentalClausesResource::collection($rentalAgreement->clauses->where('type', 'rental')),
                                        'services'         => ServiceClausesResource::collection($rentalAgreement->clauses->where('type', 'service')),
                                        'physical_goods'   => ProductClausesResource::collection($rentalAgreement->clauses->where('type', 'product')),
                                        'clauses'          => $rentalAgreement->clauses,
                                        // 'indexRentalRoute' => [
                                        //     'name'       => 'grp.org.fulfilments.show.assets.rentals.index',
                                        //     'parameters' => [
                                        //         'organisation' => $this->organisation->slug,
                                        //         'fulfilment'   => $rentalAgreement->fulfilment->slug
                                        //     ]
                                        // ],
                                        'value'          => $rentals



                                    ],
                                ]
                            ]
                        ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.fulfilment-customer.rental-agreements.update',
                            'parameters' => [
                                'fulfilmentCustomer' => $rentalAgreement->fulfilment_customer_id,
                                'rentalAgreement'    => $rentalAgreement->id,
                            ]
                        ],
                    ]
                ],
            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    /**
     * @throws Exception
     */
    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): Response
    {
        $rentalAgreement = $fulfilmentCustomer->rentalAgreement;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($rentalAgreement, $request);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            ShowFulfilmentCustomer::make()->getBreadcrumbs(
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('Creating rental agreement'),
                    ]
                ]
            ]
        );
    }

}

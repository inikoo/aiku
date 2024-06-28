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
use App\Http\Resources\Catalogue\OutersResource;
use App\Http\Resources\Catalogue\RentalsResource;
use App\Http\Resources\Catalogue\ServicesResource;
use App\Models\CRM\WebUser;
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
        $clauses = null;
        foreach ($rentalAgreement->fulfilmentCustomer->rentalAgreementClauses as $clause) {
            $price                                  = $clause->asset->price;
            $percentageOff                          = $clause->percentage_off;
            $clauses[$clause->asset->type->value][] = [
                'asset_id'       => $clause->asset_id,
                'agreed_price'   => $price * $percentageOff / 100,
                'price'          => $price,
                'percentage_off' => $percentageOff
            ];
        }


        /** @var WebUser $webUser */
        $webUser             = $rentalAgreement->fulfilmentCustomer->customer->webUsers()->first();
        $createWebUserFields = [
            'email' => [
                'type'        => 'input',
                'label'       => __('email'),
                'required'    => true,
                'value'       => $webUser->email
            ],
            'username' => [
                'type'        => 'input',
                'label'       => __('username'),
                'required'    => true,
                'value'       => $webUser->username
            ],
        ];


        return Inertia::render(
            'EditModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'       => __('edit rental agreement'),
                'pageHead'    => [
                    'title' => __('edit rental agreement')
                ],
                'formData'    => [
                    'fullLayout' => true,
                    'blueprint'  =>
                        [
                            [
                                'title'  => __(''),
                                'fields' => [
                                    'billing_cycle' => [
                                        'type'     => 'select',
                                        'label'    => __('billing cycle'),
                                        'required' => true,
                                        'options'  => Options::forEnum(RentalAgreementBillingCycleEnum::class),
                                        'value'    => $rentalAgreement->billing_cycle
                                    ],
                                    'pallets_limit' => [
                                        'type'        => 'input',
                                        'label'       => __('pallets limit'),
                                        'placeholder' => '0',
                                        'required'    => false,
                                        'value'       => $rentalAgreement->pallets_limit
                                    ],
                                    ...$createWebUserFields,
                                    'clauses'       => [
                                        'type'           => 'rental',
                                        'label'          => __('Clauses'),
                                        'required'       => false,
                                        'full'           => true,
                                        'rentals'        => RentalsResource::collection($rentalAgreement->fulfilment->rentals),
                                        'services'       => ServicesResource::collection($rentalAgreement->fulfilment->shop->services),
                                        'physical_goods' => OutersResource::collection($rentalAgreement->fulfilment->shop->products),
                                        'value'          => $clauses
                                    ]


                                ]
                            ]
                        ],
                    'args'       => [
                        'updateRoute' => [
                            'name'       => 'grp.models.rental-agreement.update',
                            'parameters' => [
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

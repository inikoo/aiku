<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Rental\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\OrgAction;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\RentalAgreement;
use App\Models\SysAdmin\Organisation;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditRental extends OrgAction
{
    /**
     * @throws Exception
     */
    public function handle(RentalAgreement $rentalAgreement, ActionRequest $request): Response
    {
        $rentals = [];

        foreach ($rentalAgreement->fulfilmentCustomer->rentalAgreementClauses as $clause) {
            $price       = $clause->rental->product->main_outerable_price;
            $agreedPrice = $clause->agreed_price;

            $rentals[] = [
                'rental'       => $clause->rental_id,
                'agreed_price' => $agreedPrice,
                'price'        => $price,
                'discount'     => ($price - $agreedPrice) / $agreedPrice * 100
            ];
        }

        return Inertia::render(
            'EditModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'    => __('edit rental'),
                'pageHead' => [
                    'title' => __('edit rental')
                ],
                'formData' => [
                    'fullLayout' => true,
                    'blueprint'  =>
                        [
                            [
                                'title'  => __('name'),
                                'fields' => [
                                    'price' => [
                                        'type'       => 'input',
                                        'label'      => __('price'),
                                        'required'   => true
                                    ],
                                    'unit' => [
                                        'type'     => 'input',
                                        'label'    => __('unit'),
                                        'required' => true
                                    ]
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
                        'label' => __('creating rental agreement'),
                    ]
                ]
            ]
        );
    }

}

<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:38:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Accounting;

use App\Models\Catalogue\Shop;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property string $quantity
 * @property string $net_amount
 * @property string $name
 * @property string $currency_code
 * @property mixed $id
 * @property mixed $in_process
 */
class InvoiceTransactionsResource extends JsonResource
{
    public function toArray($request): array
    {

        $getRoute = function ($type, $slug) use ($request) {
            $org = $request->route('organisation');
            $fulfilment = $request->route('fulfilment');
            // if (str_starts_with($request->route()->getName(), 'retina')) {
            //     // return null;
            //     return match ($type) {
            //         'Product' => [
            //             'name' => 'grp.org.fulfilments.show.catalogue.physical_goods.show',
            //             'parameters' => [
            //                 'organisation' => $org->slug,
            //                 'fulfilment' => $fulfilment->slug,
            //                 'product' => $slug
            //             ]
            //         ],
            //         'Rental' => [
            //             'name' => 'grp.org.fulfilments.show.catalogue.rentals.show',
            //             'parameters' => [
            //                 'organisation' => $org->slug,
            //                 'fulfilment' => $fulfilment->slug,
            //                 'rental' => $slug
            //             ]
            //         ],
            //         'Service' => [
            //             'name' => 'grp.org.fulfilments.show.catalogue.services.show',
            //             'parameters' => [
            //                 'organisation' => $org->slug,
            //                 'fulfilment' => $fulfilment->slug,
            //                 'service' => $slug
            //             ]
            //         ],
            //         default => null,
            //     };
            // }

            if (!$fulfilment) {
                return null;
                // return match ($type) {
                //     'Product' => [
                //         'name' => 'grp.org.shops.show.catalogue.products.current_products.show',
                //         'parameters' => [
                //             'organisation' => $org->slug,
                //             'shop' => ,
                //             'product' => $slug
                //         ]
                //     ],
                //     'Rental' => [
                //         'name' => 'grp.org.fulfilments.show.catalogue.rentals.show',
                //         'parameters' => [
                //             'organisation' => $org->slug,
                //             'fulfilment' => $fulfilment->slug,
                //             'rental' => $slug
                //         ]
                //     ],
                //     'Service' => [
                //         'name' => 'grp.org.fulfilments.show.catalogue.services.show',
                //         'parameters' => [
                //             'organisation' => $org->slug,
                //             'fulfilment' => $fulfilment->slug,
                //             'service' => $slug
                //         ]
                //     ],
                //     default => null,
                // };
            }


            return match ($type) {
                'Product' => [
                    'name' => 'grp.org.fulfilments.show.catalogue.physical_goods.show',
                    'parameters' => [
                        'organisation' => $org->slug,
                        'fulfilment' => $fulfilment->slug,
                        'product' => $slug
                    ]
                ],
                'Rental' => [
                    'name' => 'grp.org.fulfilments.show.catalogue.rentals.show',
                    'parameters' => [
                        'organisation' => $org->slug,
                        'fulfilment' => $fulfilment->slug,
                        'rental' => $slug
                    ]
                ],
                'Service' => [
                    'name' => 'grp.org.fulfilments.show.catalogue.services.show',
                    'parameters' => [
                        'organisation' => $org->slug,
                        'fulfilment' => $fulfilment->slug,
                        'service' => $slug
                    ]
                ],
                default => null,
            };
        };


        return [
            'code'                      => $this->code,
            'name'                      => $this->name,
            'quantity'                  => (int) $this->quantity,
            'net_amount'                => $this->net_amount,
            'currency_code'             => $this->currency_code,
            'in_process'                => $this->in_process,
            'route_desc'                => $getRoute($this->model_type, $this->slug),
            'refund_route'              => [
                'name'       => 'grp.models.invoice_transaction.refund_transaction.store',
                'parameters' => [
                    'invoiceTransaction' => $this->id,
                ]
            ],
        ];
    }
}

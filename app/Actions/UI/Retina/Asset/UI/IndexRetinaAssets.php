<?php
/*
 * author Arya Permana - Kirin
 * created on 10-01-2025-14h-11m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\UI\Retina\Asset\UI;

use App\Actions\RetinaAction;
use App\Actions\UI\Retina\Storage\UI\ShowRetinaStorageDashboard;
use App\Http\Resources\Helpers\CurrencyResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexRetinaAssets extends RetinaAction
{
    public function asController(ActionRequest $request)
    {
        $this->initialisation($request);

        return $this->handle($request);
    }

    public function handle(ActionRequest $request): Response
    {
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $fulfilment = $fulfilmentCustomer->fulfilment;
        $shop = $fulfilment->shop;


        $assets = [];
        foreach ($shop->assets as $asset) {
            if ($asset->type->value == 'charge') {
                continue;
            }
            $price = $asset->price;
            $assets[] = [
                'name'  => $asset->name,
                'type'  => $asset->type,
                'price' => $price,
            ];
        }

        return Inertia::render(
            'Storage/RetinaStorageAssets',
            [
                'title'                         => __('Assets'),
                'breadcrumbs'                   => $this->getBreadcrumbs(
                    request()->route()->getName(),
                ),
                'pageHead'                      => [
                    'icon'          => [
                        'icon'    => ['fal', 'fa-pallet'],
                        'tooltip' => __('Pallet')
                    ],
                    'title'         => 'Assets',
                ],
                'currency'     => CurrencyResource::make($fulfilmentCustomer->fulfilment->shop->currency),

                'assets' => $assets
            ]);
    }

    public function getBreadcrumbs(string $routeName): array
    {
        return match ($routeName) {
            'retina.storage.assets.index' =>
            array_merge(
                ShowRetinaStorageDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'retina.storage.assets.index',
                            ],
                            'label' => __('assets'),
                            'icon'  => 'fal fa-bars',
                        ],

                    ]
                ]
            ),
        };
    }
}

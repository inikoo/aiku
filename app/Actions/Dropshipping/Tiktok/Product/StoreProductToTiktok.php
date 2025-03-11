<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 10 Mar 2025 16:53:20 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Tiktok\Product;

use App\Actions\Dropshipping\Portfolio\StorePortfolio;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Portfolio\PortfolioTypeEnum;
use App\Models\Dropshipping\Portfolio;
use App\Models\Dropshipping\TiktokUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreProductToTiktok extends RetinaAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(TiktokUser $tiktokUser, array $modelData)
    {
        DB::transaction(function () use ($tiktokUser, $modelData) {
            foreach (Arr::get($modelData, 'products') as $product) {

                /** @var Portfolio $portfolio */
                $portfolio = StorePortfolio::run($tiktokUser->customer, [
                    'product_id' => $product,
                    'type' => PortfolioTypeEnum::TIKTOK->value,
                ]);

                $productData = [
                    'title' => $portfolio->item->name,
                    'description' => $portfolio->item->name,
                    'price' => $portfolio->item->price,
                    'category_id' => "2347024",
                    'main_images' => [['uri' => 'tos-useast2a-i-tulkllf4y5-euttp/bdf7a5d9a83f4f01b517995c37df7d76']],
                    'package_weight' => [
                        'value' => $portfolio->item->gross_weight ?? "1.00",
                        'unit' => 'POUND'
                    ],
                    'package_dimensions' => [
                        'width' => "1",
                        'length' => "1",
                        'height' => "1",
                        'unit' => "INCH",
                    ],
                    'skus' => [
                        [
                            'sales_attributes' => [],
                            'inventory' => [
                                [
                                    'quantity' => $portfolio->item->available_quantity,
                                    'warehouse_id' => "7480392764145878817"
                                ]
                            ],
                            'price' => [
                                'amount' => $portfolio->item->price,
                                'currency' => $tiktokUser->customer->shop->currency->code
                            ],
                        ]
                    ]
                ];

                $path = '/product/202309/products';
                $tiktok = $tiktokUser->restApi($path, $productData)
                    ->post($path, $productData);

                dd($tiktok->json());
            }
        });
    }

    public function rules(): array
    {
        return [
            'products' => ['required', 'array']
        ];
    }

    public function asController(TiktokUser $tiktokUser, ActionRequest $request): void
    {
        $this->initialisation($request);

        $this->handle($tiktokUser, $this->validatedData);
    }
}

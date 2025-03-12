<?php

/*
 * author Arya Permana - Kirin
 * created on 19-12-2024-11h-33m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Billables\Charge;

use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateInvoicedCustomers;
use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateInvoices;
use App\Actions\HydrateModel;
use App\Actions\Traits\Hydrators\WithHydrateCommand;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Models\Billables\Charge;
use App\Models\Catalogue\Asset;

class HydrateCharge extends HydrateModel
{
    use WithHydrateCommand;

    public string $commandSignature = 'hydrate:charges {organisations?*} {--slugs=}';

    public function __construct()
    {
        $this->model = Charge::class;
    }

    public function handle(Asset $asset): void
    {
        if ($asset->type == AssetTypeEnum::CHARGE) {
            AssetHydrateInvoices::run($asset);
            AssetHydrateInvoicedCustomers::run($asset);
        }

    }
}

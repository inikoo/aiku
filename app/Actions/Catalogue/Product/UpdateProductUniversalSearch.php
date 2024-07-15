<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 17:38:48 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\Charge\Hydrators\ChargeHydrateUniversalSearch;
use App\Actions\Catalogue\Product\Hydrators\ProductHydrateUniversalSearch;
use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Product;
use App\Models\SupplyChain\Supplier;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateProductUniversalSearch
{
    use asAction;

    public string $commandSignature = 'products:search';

    public function handle(Product $product): void
    {
        ProductHydrateUniversalSearch::run($product);
    }

    public function asCommand(Command $command): int
    {
        $command->withProgressBar(Product::all(), function (Product $product) {
            $this->handle($product);
        });
        return 0;
    }
}

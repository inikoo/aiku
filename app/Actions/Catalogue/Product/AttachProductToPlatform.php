<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Jun 2024 15:13:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\OrgAction;
use App\Models\Catalogue\Product;
use App\Models\Ordering\Platform;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class AttachProductToPlatform extends OrgAction
{
    /**
     * @var \App\Models\Catalogue\Product
     */
    private Product $product;

    public function handle(Product $product, Platform $platform, array $pivotData): Product
    {
        $pivotData['group_id']        = $this->organisation->group_id;
        $pivotData['organisation_id'] = $this->organisation->id;
        $pivotData['shop_id']         = $product->shop_id;
        $product->platforms()->attach($platform->id, $pivotData);


        return $product;
    }

    public function rules(): array
    {
        return [

        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if($this->product->platforms()->count() >= 1) {
            abort(403);
        }
    }

    public function action(Product $product, Platform $platform, array $modelData): Product
    {
        $this->product = $product;
        $this->initialisation($product->organisation, $modelData);

        return $this->handle($product, $platform, $this->validatedData);
    }

    public function asController(Organisation $organisation, Product $product, Platform $platform, ActionRequest $request): void
    {
        $this->initialisation($organisation, $request);
        $this->handle($product, $platform, $this->validatedData);
    }
}

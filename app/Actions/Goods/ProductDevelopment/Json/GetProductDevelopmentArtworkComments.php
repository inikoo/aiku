<?php

/*
 * Author Louis Perez
 * Created on 23-10-2026-11h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Json;

use App\Actions\Goods\ProductDevelopment\UI\GetProductDevelopmentComments;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentArtwork;
use Lorisleiva\Actions\ActionRequest;

/**
 * The discussion on one SKU's artwork, shown in the Design tab's side panel.
 */
class GetProductDevelopmentArtworkComments extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    /**
     * @return array<string, mixed>
     */
    public function handle(ProductDevelopment $productDevelopment, ProductDevelopmentArtwork $artwork): array
    {
        abort_unless($artwork->product_development_id === $productDevelopment->id, 404);

        return GetProductDevelopmentComments::run($productDevelopment, null, $artwork);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ProductDevelopment $productDevelopment, ProductDevelopmentArtwork $artwork, ActionRequest $request): array
    {
        $this->initialisationFromGroup(group(), $request);

        return $this->handle($productDevelopment, $artwork);
    }
}

<?php

/*
 * Author Louis Perez
 * Created on 22-10-2026-16h-30m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentArtworks;
use App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignImages;
use App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignLayout;
use App\Actions\Goods\ProductDevelopment\Design\UpdateProductDevelopmentArtworkLayout;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentArtwork;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * The Aiku editor for one SKU's artwork in one design section: a box built face by face, or a flat
 * page, with the SKU's label and pack information beside it.
 */
class ShowProductDevelopmentArtwork extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public function handle(ProductDevelopment $productDevelopment, ProductDevelopmentArtwork $artwork): ProductDevelopmentArtwork
    {
        abort_unless($artwork->product_development_id === $productDevelopment->id && $artwork->source === ProductDevelopmentArtwork::SOURCE_AIKU, 404);

        return $artwork->load(['section', 'item.tradeUnit', 'updatedBy', 'reviewedBy']);
    }

    public function asController(ProductDevelopment $productDevelopment, ProductDevelopmentArtwork $artwork, ActionRequest $request): ProductDevelopmentArtwork
    {
        $this->initialisationFromGroup(group(), $request);

        return $this->handle($productDevelopment, $artwork);
    }

    public function htmlResponse(ProductDevelopmentArtwork $artwork, ActionRequest $request): Response
    {
        $productDevelopment = $artwork->productDevelopment;
        $canEdit            = $this->productDevelopmentAccess()->canManageArtwork($request->user(), $productDevelopment) && UpdateProductDevelopmentArtworkLayout::isEditable($artwork);

        return Inertia::render(
            'Goods/ProductDevelopmentArtwork',
            [
                'title'       => $productDevelopment->code.' '.ProductDevelopmentArtworks::label($artwork),
                'breadcrumbs' => ShowProductDevelopmentDesign::make()->getBreadcrumbs($productDevelopment),
                'pageHead'    => [
                    'icon'  => [
                        'title' => __('Artwork'),
                        'icon'  => 'fal fa-pencil-ruler',
                    ],
                    'model' => $productDevelopment->code,
                    'title' => ProductDevelopmentArtworks::label($artwork),
                ],
                'artwork'     => [
                    'id'          => $artwork->id,
                    'section'     => $artwork->section->name,
                    'sku'         => ['code' => $artwork->item->tradeUnit->code, 'name' => $artwork->item->tradeUnit->name],
                    'version'     => $artwork->version,
                    'status'      => ProductDevelopmentArtworks::statusPayload($artwork),
                    'layout'      => ProductDevelopmentDesignLayout::normalise($artwork->layout ?? []),
                    'review_comment' => $artwork->review_comment,
                    'reviewed_by' => $artwork->reviewedBy?->contact_name ?: $artwork->reviewedBy?->username,
                    'can_edit'    => $canEdit,
                    'routes'      => [
                        'layout' => ['name' => 'grp.models.product_development.artwork.layout', 'parameters' => ['productDevelopmentArtwork' => $artwork->id]],
                        'image'  => ['name' => 'grp.models.product_development.artwork.image', 'parameters' => ['productDevelopmentArtwork' => $artwork->id]],
                        'submit' => ['name' => 'grp.models.product_development.artwork.submit', 'parameters' => ['productDevelopmentArtwork' => $artwork->id]],
                        'pdf'    => ['name' => 'grp.product_developments.artwork.pdf', 'parameters' => ['productDevelopment' => $productDevelopment->slug, 'artwork' => $artwork->id]],
                    ],
                ],
                'labelContent'     => GetProductDevelopmentLabelContent::run($productDevelopment, $artwork->item),
                'images'           => ProductDevelopmentDesignImages::forDevelopment($productDevelopment),
                'dataSources'      => ProductDevelopmentDesignLayout::dataSources(),
                'developmentRoute' => ['name' => 'grp.product_developments.show', 'parameters' => ['productDevelopment' => $productDevelopment->slug, 'tab' => 'design']],
            ]
        );
    }
}

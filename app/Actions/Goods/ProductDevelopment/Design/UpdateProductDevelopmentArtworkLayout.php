<?php

/*
 * Author Louis Perez
 * Created on 22-10-2026-15h-30m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentArtworkStatusEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentFileScopeEnum;
use App\Models\Goods\ProductDevelopmentArtwork;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Autosave of an artwork built in the Aiku editor, while it is not in review or approved.
 */
class UpdateProductDevelopmentArtworkLayout extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ProductDevelopmentArtwork $artwork;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $productDevelopment = $this->artwork->productDevelopment;

        return $this->canViewProductDevelopment($request->user(), $productDevelopment)
            && $this->productDevelopmentAccess()->canManageArtwork($request->user(), $productDevelopment);
    }

    public function handle(ProductDevelopmentArtwork $artwork, array $modelData): ProductDevelopmentArtwork
    {
        $artwork->update(['layout' => ProductDevelopmentDesignLayout::normalise($modelData['layout'])]);

        return $artwork;
    }

    public static function isEditable(ProductDevelopmentArtwork $artwork): bool
    {
        return $artwork->source === ProductDevelopmentArtwork::SOURCE_AIKU
            && in_array($artwork->status, [ProductDevelopmentArtworkStatusEnum::NOT_SUBMITTED, ProductDevelopmentArtworkStatusEnum::CHANGES_REQUIRED], true)
            && ProductDevelopmentArtworks::canChange($artwork);
    }

    public function rules(): array
    {
        return ProductDevelopmentDesignLayout::rules();
    }

    public function afterValidator(Validator $validator): void
    {
        if (!self::isEditable($this->artwork)) {
            $validator->errors()->add('layout', ProductDevelopmentDesignRounds::isArtworkLocked($this->artwork->productDevelopment)
                ? __('The artwork is signed off and locked')
                : __('This artwork cannot be changed now'));

            return;
        }

        $imageIds = ProductDevelopmentDesignLayout::imageFileIds((array) $this->get('layout'));
        if ($imageIds === []) {
            return;
        }

        $known = $this->artwork->productDevelopment->files()
            ->where('scope', ProductDevelopmentFileScopeEnum::DESIGN_IMAGE->value)
            ->whereIn('id', $imageIds)
            ->count();
        if ($known !== count($imageIds)) {
            $validator->errors()->add('layout', __('An image on this design is not one of this development\'s design images'));
        }
    }

    public function asController(ProductDevelopmentArtwork $productDevelopmentArtwork, ActionRequest $request): ProductDevelopmentArtwork
    {
        $this->artwork = $productDevelopmentArtwork;
        $this->initialisationFromGroup($productDevelopmentArtwork->productDevelopment->group, $request);

        return $this->handle($productDevelopmentArtwork, $this->validatedData);
    }

    public function action(ProductDevelopmentArtwork $artwork, array $modelData): ProductDevelopmentArtwork
    {
        $this->asAction = true;
        $this->artwork  = $artwork;
        $this->initialisationFromGroup($artwork->productDevelopment->group, $modelData);

        return $this->handle($artwork, $this->validatedData);
    }

    /**
     * @return array{saved: bool}
     */
    public function jsonResponse(): array
    {
        return ['saved' => true];
    }
}

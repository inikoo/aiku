<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-11h-10m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Actions\Goods\ProductDevelopment\UploadProductDevelopmentFiles;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentFileScopeEnum;
use App\Models\Goods\ProductDevelopmentArtwork;
use App\Models\Goods\ProductDevelopmentFile;
use App\Models\SysAdmin\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class UploadProductDevelopmentDesignImage extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public const string ACCEPTED_EXTENSIONS = 'jpg,jpeg,png,webp';

    public const int MAX_KILOBYTES = 20480;

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

    public function handle(ProductDevelopmentArtwork $artwork, User $user, UploadedFile $image): ProductDevelopmentFile
    {
        $productDevelopment = $artwork->productDevelopment;
        $media              = UploadProductDevelopmentFiles::make()->storeMedia($productDevelopment, $image, ProductDevelopmentFileScopeEnum::DESIGN_IMAGE, null);

        /** @var ProductDevelopmentFile $file */
        $file = $productDevelopment->files()->create([
            'media_id'        => $media->id,
            'scope'           => ProductDevelopmentFileScopeEnum::DESIGN_IMAGE,
            'version'         => 1,
            'uploaded_by_id'  => $user->id,
        ]);

        return $file;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'max:'.self::MAX_KILOBYTES, 'extensions:'.self::ACCEPTED_EXTENSIONS, 'mimes:'.self::ACCEPTED_EXTENSIONS],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!UpdateProductDevelopmentArtworkLayout::isEditable($this->artwork)) {
            $validator->errors()->add('image', __('This artwork cannot be changed now'));
        }
    }

    public function asController(ProductDevelopmentArtwork $productDevelopmentArtwork, ActionRequest $request): ProductDevelopmentFile
    {
        $this->artwork = $productDevelopmentArtwork;
        $this->initialisationFromGroup($productDevelopmentArtwork->productDevelopment->group, $request);

        return $this->handle($productDevelopmentArtwork, $request->user(), $this->validatedData['image']);
    }

    public function action(ProductDevelopmentArtwork $artwork, User $user, array $modelData): ProductDevelopmentFile
    {
        $this->asAction    = true;
        $this->artwork  = $artwork;
        $this->initialisationFromGroup($artwork->productDevelopment->group, $modelData);

        return $this->handle($artwork, $user, $this->validatedData['image']);
    }

    /**
     * @return array{id: int, url: string|null, name: string|null}
     */
    public function jsonResponse(ProductDevelopmentFile $file): array
    {
        return ProductDevelopmentDesignImages::row($file->load('media'));
    }
}

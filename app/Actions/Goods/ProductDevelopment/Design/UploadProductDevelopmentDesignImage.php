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
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundStatusEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentFileScopeEnum;
use App\Models\Goods\ProductDevelopmentDesignRound;
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

    private ProductDevelopmentDesignRound $designRound;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $productDevelopment = $request->route('productDevelopmentDesignRound')->productDevelopment;

        return $this->canViewProductDevelopment($request->user(), $productDevelopment)
            && $this->productDevelopmentAccess()->canDesign($request->user(), $productDevelopment);
    }

    public function handle(ProductDevelopmentDesignRound $designRound, User $user, UploadedFile $image): ProductDevelopmentFile
    {
        $productDevelopment = $designRound->productDevelopment;
        $media              = UploadProductDevelopmentFiles::make()->storeMedia($productDevelopment, $image, ProductDevelopmentFileScopeEnum::DESIGN_IMAGE, null);

        /** @var ProductDevelopmentFile $file */
        $file = $productDevelopment->files()->create([
            'design_round_id' => $designRound->id,
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
        if (ProductDevelopmentDesignRounds::isArtworkLocked($this->designRound->productDevelopment)) {
            $validator->errors()->add('image', __('The artwork is signed off and locked'));

            return;
        }

        if ($this->designRound->status !== ProductDevelopmentDesignRoundStatusEnum::DRAFT) {
            $validator->errors()->add('image', __('Only a draft version can be changed'));
        }
    }

    public function asController(ProductDevelopmentDesignRound $productDevelopmentDesignRound, ActionRequest $request): ProductDevelopmentFile
    {
        $this->designRound = $productDevelopmentDesignRound;
        $this->initialisationFromGroup($productDevelopmentDesignRound->productDevelopment->group, $request);

        return $this->handle($productDevelopmentDesignRound, $request->user(), $this->validatedData['image']);
    }

    public function action(ProductDevelopmentDesignRound $designRound, User $user, array $modelData): ProductDevelopmentFile
    {
        $this->asAction    = true;
        $this->designRound = $designRound;
        $this->initialisationFromGroup($designRound->productDevelopment->group, $modelData);

        return $this->handle($designRound, $user, $this->validatedData['image']);
    }

    /**
     * @return array{id: int, url: string|null, name: string|null}
     */
    public function jsonResponse(ProductDevelopmentFile $file): array
    {
        return ProductDevelopmentDesignImages::row($file->load('media'));
    }
}

<?php

/*
 * Author Louis Perez
 * Created on 18-10-2026-01h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Actions\Goods\ProductDevelopment\Hydrators\ProductDevelopmentHydrateChecklist;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistItemStateEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentFileScopeEnum;
use App\Models\Goods\ProductDevelopmentChecklistItem;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * One angle of the factory photos of a SKU. The photo is kept as a factory photo file of the SKU, a new
 * one for an angle replaces the old (kept as an earlier version), and the checklist item is complete once
 * every angle has a photo.
 */
class UploadProductDevelopmentFactoryPhoto extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public const string KEY = 'factory_photos';

    public const array ANGLES = ['front', 'three_quarter', 'back', 'left', 'right', 'top'];

    public const int MAX_KILOBYTES = 20480;

    private ProductDevelopmentChecklistItem $checklistItem;

    /**
     * @return array<string, string>
     */
    public static function angleLabels(): array
    {
        return [
            'front'         => __('Front'),
            'three_quarter' => __('3/4 view'),
            'back'          => __('Back'),
            'left'          => __('Left'),
            'right'         => __('Right'),
            'top'           => __('Top'),
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        /** @var ProductDevelopmentChecklistItem $checklistItem */
        $checklistItem = $request->route('productDevelopmentChecklistItem');

        return $this->canViewProductDevelopment($request->user(), $checklistItem->productDevelopment)
            && $this->productDevelopmentAccess()->canEditChecklistItem($request->user(), $checklistItem);
    }

    public function handle(ProductDevelopmentChecklistItem $checklistItem, User $user, string $angle, UploadedFile $photo): ProductDevelopmentChecklistItem
    {
        $productDevelopment = $checklistItem->productDevelopment;
        $item               = $checklistItem->item;

        DB::transaction(function () use ($checklistItem, $user, $angle, $photo, $productDevelopment, $item) {
            $angles = $checklistItem->value['angles'] ?? [];

            if ($previousId = $angles[$angle] ?? null) {
                $productDevelopment->files()->whereKey($previousId)->whereNull('superseded_at')->update(['superseded_at' => now()]);
            }

            $media = UploadProductDevelopmentFiles::make()->storeMedia($productDevelopment, $photo, ProductDevelopmentFileScopeEnum::FACTORY_PHOTO, $item);
            $file  = $productDevelopment->files()->create([
                'product_development_item_id' => $item?->id,
                'checklist_item_id'           => $checklistItem->id,
                'media_id'                    => $media->id,
                'scope'                       => ProductDevelopmentFileScopeEnum::FACTORY_PHOTO,
                'version'                     => (int) $productDevelopment->files()->where('checklist_item_id', $checklistItem->id)->count() + 1,
                'uploaded_by_id'              => $user->id,
            ]);

            $angles[$angle] = $file->id;
            $complete       = collect(self::ANGLES)->every(fn (string $required) => !empty($angles[$required]));

            $checklistItem->update([
                'value'           => ['angles' => $angles],
                'state'           => $complete ? ProductDevelopmentChecklistItemStateEnum::COMPLETE : ProductDevelopmentChecklistItemStateEnum::MISSING,
                'completed_by_id' => $complete ? $user->id : null,
                'completed_at'    => $complete ? now() : null,
            ]);

            RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::FILES_UPLOADED, [], [
                'scope' => ProductDevelopmentFileScopeEnum::FACTORY_PHOTO->value,
                'sku'   => $item?->tradeUnit?->code,
                'files' => [self::angleLabels()[$angle].': '.$photo->getClientOriginalName()],
            ]);
        });

        ProductDevelopmentHydrateChecklist::run($productDevelopment);
        CreateProductDevelopmentTask::make()->syncProgress($productDevelopment, $user);

        return $checklistItem->refresh();
    }

    public function rules(): array
    {
        return [
            'angle' => ['required', Rule::in(self::ANGLES)],
            'photo' => ['required', 'file', 'max:'.self::MAX_KILOBYTES, 'extensions:jpg,jpeg,png,webp,heic,heif'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if ($this->checklistItem->key !== self::KEY) {
            $validator->errors()->add('photo', __('This checklist item does not take factory photos'));
        }
    }

    public function asController(ProductDevelopmentChecklistItem $productDevelopmentChecklistItem, ActionRequest $request): ProductDevelopmentChecklistItem
    {
        $this->checklistItem = $productDevelopmentChecklistItem;
        $this->initialisationFromGroup($productDevelopmentChecklistItem->productDevelopment->group, $request);

        return $this->handle($productDevelopmentChecklistItem, $request->user(), $this->validatedData['angle'], $this->validatedData['photo']);
    }

    public function action(ProductDevelopmentChecklistItem $checklistItem, User $user, array $modelData): ProductDevelopmentChecklistItem
    {
        $this->asAction      = true;
        $this->checklistItem = $checklistItem;
        $this->initialisationFromGroup($checklistItem->productDevelopment->group, $modelData);

        return $this->handle($checklistItem, $user, $this->validatedData['angle'], $this->validatedData['photo']);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}

<?php

/*
 * Author Louis Perez
 * Created on 18-10-2026-03h-30m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Actions\Goods\ProductDevelopment\RecordProductDevelopmentActivity;
use App\Actions\Goods\ProductDevelopment\UploadProductDevelopmentFiles;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundStatusEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentFileScopeEnum;
use App\Models\Goods\ProductDevelopmentDesignRound;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * The one PDF or picture of an uploaded design version. Uploading again replaces it while the version
 * is a draft; the replaced file stays as an earlier artwork draft.
 */
class UploadProductDevelopmentDesignArtwork extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public const string ACCEPTED_EXTENSIONS = 'pdf,jpg,jpeg,png,webp';

    public const int MAX_KILOBYTES = 51200;

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

    public function handle(ProductDevelopmentDesignRound $designRound, User $user, UploadedFile $file): ProductDevelopmentDesignRound
    {
        $productDevelopment = $designRound->productDevelopment;

        DB::transaction(function () use ($designRound, $user, $file, $productDevelopment) {
            $data = $designRound->data;
            if ($previousId = $data['upload']['file_id'] ?? null) {
                $productDevelopment->files()->whereKey($previousId)->whereNull('superseded_at')->update(['superseded_at' => now()]);
            }

            $media  = UploadProductDevelopmentFiles::make()->storeMedia($productDevelopment, $file, ProductDevelopmentFileScopeEnum::ARTWORK_DRAFT, null);
            $stored = $productDevelopment->files()->create([
                'design_round_id'             => $designRound->id,
                'media_id'                    => $media->id,
                'scope'                       => ProductDevelopmentFileScopeEnum::ARTWORK_DRAFT,
                'version'                     => $designRound->version,
                'uploaded_by_id'              => $user->id,
            ]);

            $isPdf          = strtolower($file->getClientOriginalExtension()) === 'pdf' || $file->getMimeType() === 'application/pdf';
            $data['source'] = StoreProductDevelopmentDesignVersion::SOURCE_UPLOAD;
            $data['upload'] = ['file_id' => $stored->id, 'kind' => $isPdf ? 'pdf' : 'image', 'name' => $file->getClientOriginalName()];
            $designRound->update(['data' => $data]);

            RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::FILES_UPLOADED, [], [
                'scope'   => ProductDevelopmentFileScopeEnum::ARTWORK_DRAFT->value,
                'version' => $designRound->version,
                'files'   => [$file->getClientOriginalName()],
            ]);
        });

        return $designRound->refresh();
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:'.self::MAX_KILOBYTES, 'extensions:'.self::ACCEPTED_EXTENSIONS],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if ($this->designRound->status !== ProductDevelopmentDesignRoundStatusEnum::DRAFT) {
            $validator->errors()->add('file', __('Only a draft version can be changed'));
        }
    }

    public function asController(ProductDevelopmentDesignRound $productDevelopmentDesignRound, ActionRequest $request): ProductDevelopmentDesignRound
    {
        $this->designRound = $productDevelopmentDesignRound;
        $this->initialisationFromGroup($productDevelopmentDesignRound->productDevelopment->group, $request);

        return $this->handle($productDevelopmentDesignRound, $request->user(), $this->validatedData['file']);
    }

    public function action(ProductDevelopmentDesignRound $designRound, User $user, array $modelData): ProductDevelopmentDesignRound
    {
        $this->asAction    = true;
        $this->designRound = $designRound;
        $this->initialisationFromGroup($designRound->productDevelopment->group, $modelData);

        return $this->handle($designRound, $user, $this->validatedData['file']);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}

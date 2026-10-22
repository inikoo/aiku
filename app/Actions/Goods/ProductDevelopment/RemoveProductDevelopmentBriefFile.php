<?php

/*
 * Author Louis Perez
 * Created on 22-10-2026-18h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\Goods\ProductDevelopmentFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Takes a brief reference PDF or a product reference image off the brief. The stored file stays; only
 * its place on the development goes.
 */
class RemoveProductDevelopmentBriefFile extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ProductDevelopmentFile $file;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $productDevelopment = $this->file->productDevelopment;

        return $this->canViewProductDevelopment($request->user(), $productDevelopment)
            && $this->productDevelopmentAccess()->canUpload($request->user(), $productDevelopment);
    }

    public function handle(ProductDevelopmentFile $file): void
    {
        $productDevelopment = $file->productDevelopment;
        $name               = $file->media?->name ?? __('a file');
        $file->delete();

        RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::FILE_REMOVED, ['file' => $name], []);
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->file->scope->isBrief()) {
            $validator->errors()->add('file', __('Only brief references and product reference images can be removed here'));
        }
    }

    public function asController(ProductDevelopmentFile $productDevelopmentFile, ActionRequest $request): void
    {
        $this->file = $productDevelopmentFile;
        $this->initialisationFromGroup($productDevelopmentFile->productDevelopment->group, $request);

        $this->handle($productDevelopmentFile);
    }

    public function action(ProductDevelopmentFile $file): void
    {
        $this->asAction = true;
        $this->file     = $file;
        $this->initialisationFromGroup($file->productDevelopment->group, []);

        $this->handle($file);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}

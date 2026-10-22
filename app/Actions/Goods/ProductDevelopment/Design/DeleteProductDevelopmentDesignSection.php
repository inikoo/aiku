<?php

/*
 * Author Louis Perez
 * Created on 22-10-2026-15h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Actions\Goods\ProductDevelopment\CreateProductDevelopmentTask;
use App\Actions\Goods\ProductDevelopment\RecordProductDevelopmentActivity;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\Goods\ProductDevelopmentArtwork;
use App\Models\Goods\ProductDevelopmentDesignSection;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Removes a design section the development does not need. Only an empty section can go, so no artwork
 * is ever thrown away with it.
 */
class DeleteProductDevelopmentDesignSection extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ProductDevelopmentDesignSection $section;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $productDevelopment = $this->section->productDevelopment;

        return $this->canViewProductDevelopment($request->user(), $productDevelopment)
            && $this->productDevelopmentAccess()->canManageArtwork($request->user(), $productDevelopment);
    }

    public function handle(ProductDevelopmentDesignSection $section, User $user): void
    {
        $productDevelopment = $section->productDevelopment;
        $name               = $section->name;
        $section->artworks()->delete();
        $section->delete();

        RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::DESIGN_SECTION_REMOVED, ['section' => $name], []);
        CreateProductDevelopmentTask::make()->ensureForDevelopment($productDevelopment->refresh(), $user);
    }

    public static function isEmpty(ProductDevelopmentDesignSection $section): bool
    {
        return !$section->artworks()->where(fn ($query) => $query->whereNotNull('media_id')->orWhere('source', ProductDevelopmentArtwork::SOURCE_AIKU))->exists();
    }

    public function afterValidator(Validator $validator): void
    {
        if (ProductDevelopmentDesignRounds::isArtworkLocked($this->section->productDevelopment)) {
            $validator->errors()->add('section', __('The artwork is signed off and locked'));

            return;
        }

        if (!self::isEmpty($this->section)) {
            $validator->errors()->add('section', __('Only an empty section can be removed'));
        }
    }

    public function asController(ProductDevelopmentDesignSection $productDevelopmentDesignSection, ActionRequest $request): void
    {
        $this->section = $productDevelopmentDesignSection;
        $this->initialisationFromGroup($productDevelopmentDesignSection->productDevelopment->group, $request);

        $this->handle($productDevelopmentDesignSection, $request->user());
    }

    public function action(ProductDevelopmentDesignSection $section, User $user): void
    {
        $this->asAction = true;
        $this->section  = $section;
        $this->initialisationFromGroup($section->productDevelopment->group, []);

        $this->handle($section, $user);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}

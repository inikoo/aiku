<?php

/*
 * Created: Fri, 16 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Enums\Web\Seo\SeoContentSuggestionStateEnum;
use App\Models\Catalogue\ProductCategory;
use App\Models\Web\SeoContentSuggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

/**
 * The webmaster signs off the SEO review once every suggestion is implemented, which completes
 * the family page workflow.
 */
class ValidateFamilySeoReview extends OrgAction
{
    private ProductCategory $family;

    /**
     * @throws ValidationException
     */
    public function handle(ProductCategory $family): int
    {
        $suggestions = fn () => SeoContentSuggestion::where('webpage_id', $family->webpage->id)->where('reason', SeoContentSuggestion::REASON_SEO_REVIEW);

        if ($suggestions()->where('state', SeoContentSuggestionStateEnum::PENDING)->exists()) {
            throw ValidationException::withMessages(['validate' => __('Implement every suggestion before validating.')]);
        }

        return $suggestions()->where('state', SeoContentSuggestionStateEnum::IMPLEMENTED)->update(['state' => SeoContentSuggestionStateEnum::ACCEPTED]);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return ImplementFamilySeoSuggestion::isWebmaster($request->user(), $this->family);
    }

    /**
     * @throws ValidationException
     */
    public function asController(ProductCategory $productCategory, ActionRequest $request): int
    {
        abort_unless($productCategory->webpage, 404);

        $this->family = $productCategory;
        $this->initialisationFromShop($productCategory->shop, $request);

        return $this->handle($productCategory);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}

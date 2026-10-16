<?php

/*
 * Author: Rifqi <rifqitaufiqurrohman1@gmail.com>
 * Created: Fri, 16 Oct 2026
 * Copyright (c) 2026
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Enums\Web\Seo\SeoContentSuggestionStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Web\SeoContentSuggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

/**
 * The webmaster signs off the product SEO review once every suggestion is implemented, which
 * completes the product page workflow, as ValidateFamilySeoReview does for families.
 */
class ValidateProductSeoReview extends OrgAction
{
    private Product $product;

    /**
     * @throws ValidationException
     */
    public function handle(Product $product): int
    {
        $suggestions = fn () => SeoContentSuggestion::where('webpage_id', $product->webpage->id)->where('reason', SeoContentSuggestion::REASON_SEO_REVIEW);

        if ($suggestions()->where('state', SeoContentSuggestionStateEnum::PENDING)->exists()) {
            throw ValidationException::withMessages(['validate' => __('Implement every suggestion before validating.')]);
        }

        $acceptedCount = $suggestions()->where('state', SeoContentSuggestionStateEnum::IMPLEMENTED)->update(['state' => SeoContentSuggestionStateEnum::ACCEPTED]);
        AskWebmastersToImplementSeoReview::make()->close($product->webpage);

        return $acceptedCount;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return ImplementProductSeoSuggestion::isWebmaster($request->user(), $this->product);
    }

    /**
     * @throws ValidationException
     */
    public function asController(Product $product, ActionRequest $request): int
    {
        abort_unless($product->webpage, 404);

        $this->product = $product;
        $this->initialisationFromShop($product->shop, $request);

        return $this->handle($product);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}

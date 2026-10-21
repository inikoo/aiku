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
use App\Models\SysAdmin\User;
use App\Models\Web\SeoContentSuggestion;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

/**
 * The webmaster signs off the product SEO review, which completes the product page workflow, as
 * ValidateFamilySeoReview does for families. Suggestions still pending were confirmed as skipped
 * in the UI, so they are dismissed instead of blocking the validation.
 */
class ValidateProductSeoReview extends OrgAction
{
    private Product $product;

    public function handle(Product $product, ?User $user = null): int
    {
        $suggestions = fn () => SeoContentSuggestion::where('webpage_id', $product->webpage->id)->where('reason', SeoContentSuggestion::REASON_SEO_REVIEW);

        $suggestions()->where('state', SeoContentSuggestionStateEnum::PENDING)->update([
            'state'              => SeoContentSuggestionStateEnum::DISMISSED,
            'decided_by_user_id' => $user?->id,
            'decided_at'         => now(),
        ]);

        $acceptedCount = $suggestions()->where('state', SeoContentSuggestionStateEnum::IMPLEMENTED)->get()
            ->each(fn (SeoContentSuggestion $suggestion) => $suggestion->update(['state' => SeoContentSuggestionStateEnum::ACCEPTED]))
            ->count();
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

    public function asController(Product $product, ActionRequest $request): int
    {
        abort_unless($product->webpage, 404);

        $this->product = $product;
        $this->initialisationFromShop($product->shop, $request);

        return $this->handle($product, $request->user());
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}

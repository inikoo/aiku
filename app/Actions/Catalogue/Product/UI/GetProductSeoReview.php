<?php

/*
 * Author: Rifqi <rifqitaufiqurrohman1@gmail.com>
 * Created: Fri, 16 Oct 2026
 * Copyright (c) 2026
 */

namespace App\Actions\Catalogue\Product\UI;

use App\Actions\Web\Seo\ImplementProductSeoSuggestion;
use App\Actions\Web\Seo\StoreFamilySeoReview;
use App\Actions\Web\Seo\StoreProductSeoReview;
use App\Enums\Web\Seo\SeoContentSuggestionStateEnum;
use App\Models\Catalogue\Product;
use App\Models\SysAdmin\User;
use App\Models\Web\SeoContentSuggestion;
use App\Models\Web\SeoTrackedKeyword;
use App\Models\Web\Webpage;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * The SEO team's review of a shop's product webpage, read as GetFamilyEditPage reads the family
 * one: the targeted keywords and, for every field it may suggest, the value on the page next to
 * the suggestion still waiting to be implemented or validated.
 */
class GetProductSeoReview
{
    use AsObject;

    /**
     * @return array<string, mixed>
     */
    public function handle(Webpage $webpage, Product $product, User $user): array
    {
        $suggestions = SeoContentSuggestion::where('webpage_id', $webpage->id)
            ->where('reason', SeoContentSuggestion::REASON_SEO_REVIEW)
            ->whereIn('state', [SeoContentSuggestionStateEnum::PENDING, SeoContentSuggestionStateEnum::IMPLEMENTED])
            ->orderBy('id')
            ->get()
            ->keyBy('field');

        $labels = [
            'title'            => [__('Meta title'), 'search'],
            'description'      => [__('Meta description'), 'search'],
            'url'              => [__('URL'), 'search'],
            'breadcrumb_label' => [__('Breadcrumb label'), 'search'],
            'product_name'     => [__('Product name'), 'content'],
            'top_desc'         => [__('Description'), 'content'],
            'extra_desc'       => [__('Extra description'), 'content'],
        ];

        $currentValues = [
            ...StoreProductSeoReview::currentValues($product, $webpage),
            StoreFamilySeoReview::FIELD_STRUCTURED_DATA => $this->structuredData($webpage),
        ];

        $fields = collect($currentValues)->map(function (?string $current, string $field) use ($labels, $suggestions, $product, $user) {
            /** @var SeoContentSuggestion|null $suggestion */
            $suggestion = $suggestions->get($field);

            return [
                'field'      => $field,
                'group'      => $labels[$field][1] ?? 'structured_data',
                'label'      => $labels[$field][0] ?? __('Custom JSON-LD'),
                'current'    => $current,
                'suggestion' => $suggestion ? [
                    'id'              => $suggestion->id,
                    'value'           => $field === StoreFamilySeoReview::FIELD_STRUCTURED_DATA ? json_decode($suggestion->suggestion, true)['json_ld'] ?? null : $suggestion->suggestion,
                    'is_implemented'  => $suggestion->state === SeoContentSuggestionStateEnum::IMPLEMENTED,
                    'can_implement'   => ImplementProductSeoSuggestion::canImplement($user, $product, $field),
                    'implement_route' => [
                        'name'       => 'grp.models.product.seo_review.implement',
                        'parameters' => ['product' => $product->id, 'seoContentSuggestion' => $suggestion->id],
                    ],
                ] : null,
            ];
        })->values();

        $pending = $suggestions->where('state', SeoContentSuggestionStateEnum::PENDING)->count();

        return [
            'keywords'       => SeoTrackedKeyword::where('target_webpage_id', $webpage->id)->where('is_active', true)->distinct()->orderBy('keyword')->pluck('keyword')->implode(', '),
            'pending'        => $pending,
            'implemented'    => $suggestions->count() - $pending,
            'is_validated'   => $suggestions->isEmpty() && SeoContentSuggestion::where('webpage_id', $webpage->id)
                ->where('reason', SeoContentSuggestion::REASON_SEO_REVIEW)
                ->where('state', SeoContentSuggestionStateEnum::ACCEPTED)
                ->exists(),
            'can_validate'   => ImplementProductSeoSuggestion::isWebmaster($user, $product),
            'store_route'    => ['name' => 'grp.models.product.seo_review.store', 'parameters' => ['product' => $product->id]],
            'validate_route' => ['name' => 'grp.models.product.seo_review.validate', 'parameters' => ['product' => $product->id]],
            'fields'         => $fields->all(),
        ];
    }

    private function structuredData(Webpage $webpage): ?string
    {
        $structuredData = Arr::get($webpage->seo_data, 'structured_data');

        if (is_string($structuredData)) {
            return $structuredData !== '' ? $structuredData : null;
        }

        return $structuredData ? json_encode($structuredData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
    }
}

<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 07 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo\UI;

use App\Actions\OrgAction;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Web\SeoTrackedKeyword;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * One row per keyword, country and language: the mobile and desktop checks of a keyword are one line
 * here, and changing or removing it applies to both.
 */
class IndexSeoTrackedKeywords extends OrgAction
{
    public function handle(Shop $shop, ?string $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where('seo_tracked_keywords.keyword', 'ilike', '%'.addcslashes(strip_tags($value), '%_\\').'%');
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        return QueryBuilder::for(SeoTrackedKeyword::class)
            ->where('seo_tracked_keywords.shop_id', $shop->id)
            ->leftJoin('seo_keywords', function ($join) {
                $join->on('seo_keywords.shop_id', '=', 'seo_tracked_keywords.shop_id')
                    ->on('seo_keywords.keyword', '=', 'seo_tracked_keywords.keyword')
                    ->on('seo_keywords.country_code', '=', 'seo_tracked_keywords.country_code')
                    ->on('seo_keywords.language_code', '=', 'seo_tracked_keywords.language_code');
            })
            ->leftJoin('webpages', 'webpages.id', '=', 'seo_tracked_keywords.target_webpage_id')
            ->groupBy('seo_tracked_keywords.keyword', 'seo_tracked_keywords.country_code', 'seo_tracked_keywords.language_code')
            ->defaultSort('keyword')
            ->select([
                'seo_tracked_keywords.keyword',
                'seo_tracked_keywords.country_code',
                'seo_tracked_keywords.language_code',
            ])
            ->selectRaw('MIN(seo_tracked_keywords.id) as id')
            ->selectRaw('MIN(seo_tracked_keywords.frequency) as frequency')
            ->selectRaw('BOOL_OR(seo_tracked_keywords.is_active) as is_active')
            ->selectRaw('MIN(seo_tracked_keywords.created_at) as created_at')
            ->selectRaw('MAX(seo_keywords.avg_monthly_searches) as avg_monthly_searches')
            ->selectRaw('MIN(webpages.code) as target_webpage_code')
            ->allowedSorts(['keyword', 'country_code', 'avg_monthly_searches', 'created_at'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withLabelRecord([__('keyword'), __('keywords')])
                ->withEmptyState([
                    'title'       => __('No keywords tracked yet'),
                    'description' => __('Add a keyword above, or use Track on a result in the Research tab.'),
                ])
                ->column(key: 'keyword', label: __('Keyword'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'country_code', label: __('Country'), sortable: true)
                ->column(key: 'language_code', label: __('Language'))
                ->column(key: 'frequency', label: __('Check'), tooltip: __('How often the Google position is checked: on request only when someone presses Check now in Rankings, monthly or weekly to the top 30, daily to the top 20. Results are in the Rankings tab'), tooltipIcon: true)
                ->column(key: 'avg_monthly_searches', label: __('Monthly searches'), tooltip: __('Average monthly Google searches for the country and language, from Google Ads data through DataForSEO. Refreshed monthly; a keyword added by hand gets it the next day'), sortable: true, align: 'right', tooltipIcon: true)
                ->column(key: 'is_active', label: __('Active'))
                ->column(key: 'actions', label: '', canBeHidden: false)
                ->defaultSort('keyword');
        };
    }
}

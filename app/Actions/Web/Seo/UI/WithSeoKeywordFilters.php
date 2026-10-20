<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Seo\UI;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The keyword filters of the Tracked keywords and Rankings tables, read from `{prefix}_filters`.
 * Each one is tested on the keyword's rows (a country on a device), so a keyword shows when any of
 * them matches. A metric we have no data for never matches a range, only its "no data" option.
 */
trait WithSeoKeywordFilters
{
    /**
     * @return array<string, mixed>
     */
    public function keywordFilters(?string $prefix = null): array
    {
        $filters = request()->input(($prefix ? $prefix.'_' : '').'filters', []);

        return is_array($filters) ? $filters : [];
    }

    /**
     * @param  Builder<\App\Models\Web\SeoTrackedKeyword>  $query
     * @param  array<string, mixed>  $filters
     */
    public function applyKeywordFilters(Builder $query, array $filters): void
    {
        $this->filterPosition($query, $filters);
        $this->filterChange($query, $this->strings($filters['change'] ?? []));
        $this->filterSerpFeature($query, $this->string($filters['serp_feature'] ?? ''), $this->string($filters['serp_mode'] ?? 'on_serp'));
        $this->filterTags($query, $this->strings($filters['tags'] ?? []), ($filters['tags_mode'] ?? 'include') === 'exclude');
        $this->filterRange($query, 'seo_keywords.avg_monthly_searches', $filters['volume_min'] ?? null, $filters['volume_max'] ?? null, filter_var($filters['volume_unknown'] ?? false, FILTER_VALIDATE_BOOLEAN));
        $this->filterRange($query, 'seo_keywords.keyword_difficulty', $filters['kd_min'] ?? null, $filters['kd_max'] ?? null, filter_var($filters['kd_unknown'] ?? false, FILTER_VALIDATE_BOOLEAN));
        $this->filterKeywordRules($query, Arr::wrap($filters['rules'] ?? []), ($filters['logic'] ?? 'and') === 'or');

        $intents = $this->strings($filters['intents'] ?? []);

        if ($intents !== []) {
            $query->whereIn('seo_keywords.intent', $intents);
        }
    }

    /**
     * Sets `tags` (id and name, by name) on every row.
     *
     * @param  Collection<int, \App\Models\Web\SeoTrackedKeyword>  $rows
     */
    public function attachTags(int $shopId, Collection $rows): void
    {
        $tags = DB::table('seo_keyword_tag_keyword')
            ->join('seo_keyword_tags', 'seo_keyword_tags.id', '=', 'seo_keyword_tag_keyword.tag_id')
            ->where('seo_keyword_tags.shop_id', $shopId)
            ->whereIn('seo_keyword_tag_keyword.keyword', $rows->pluck('keyword')->unique()->values())
            ->orderBy('seo_keyword_tags.name')
            ->get(['seo_keyword_tag_keyword.keyword', 'seo_keyword_tags.id', 'seo_keyword_tags.name'])
            ->groupBy('keyword');

        $rows->each(fn ($row) => $row->setAttribute('tags', $tags->get($row->keyword, collect())->map(fn ($tag) => ['id' => $tag->id, 'name' => $tag->name])->values()->all()));
    }

    private function string(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * @return array<int, string>
     */
    private function strings(mixed $values): array
    {
        return array_values(array_map('strval', array_filter(Arr::wrap($values), 'is_scalar')));
    }

    private function filterPosition(Builder $query, array $filters): void
    {
        $group = $filters['position'] ?? null;

        if ($group === 'not_ranked') {
            $query->whereNotNull('seo_tracked_keywords.last_checked_at')->whereNull('seo_tracked_keywords.position');
        } elseif (is_string($group) && preg_match('/^top_(\d+)$/', $group, $matches)) {
            $query->whereBetween('seo_tracked_keywords.position', [1, (int) $matches[1]]);
        }

        if (is_numeric($filters['position_min'] ?? null)) {
            $query->where('seo_tracked_keywords.position', '>=', (int) $filters['position_min']);
        }

        if (is_numeric($filters['position_max'] ?? null)) {
            $query->where('seo_tracked_keywords.position', '<=', (int) $filters['position_max']);
        }
    }

    /**
     * Compared with the check before the latest one.
     *
     * @param  array<int, string>  $changes
     */
    private function filterChange(Builder $query, array $changes): void
    {
        $conditions = Arr::only([
            'improved'  => 'seo_tracked_keywords.position < seo_tracked_keywords.previous_position',
            'declined'  => 'seo_tracked_keywords.position > seo_tracked_keywords.previous_position',
            'unchanged' => 'seo_tracked_keywords.position = seo_tracked_keywords.previous_position',
            'new'       => 'seo_tracked_keywords.previous_position IS NULL AND seo_tracked_keywords.position IS NOT NULL',
            'lost'      => 'seo_tracked_keywords.previous_position IS NOT NULL AND seo_tracked_keywords.position IS NULL',
        ], $changes);

        if ($conditions === []) {
            return;
        }

        $query->whereNotNull('seo_tracked_keywords.last_checked_at')
            ->whereNotNull('seo_tracked_keywords.previous_checked_at')
            ->where(function (Builder $query) use ($conditions) {
                foreach ($conditions as $condition) {
                    $query->orWhereRaw('('.$condition.')');
                }
            });
    }

    private function filterSerpFeature(Builder $query, string $feature, string $mode): void
    {
        if ($feature === '') {
            return;
        }

        $feature = json_encode([$feature]);

        match ($mode) {
            'not_on_serp' => $query->whereNotNull('seo_tracked_keywords.last_checked_at')->whereRaw('NOT (COALESCE(seo_tracked_keywords.serp_features, \'[]\')::jsonb @> ?::jsonb)', [$feature]),
            'ours'        => $query->whereRaw('COALESCE(seo_tracked_keywords.serp_features_owned, \'[]\')::jsonb @> ?::jsonb', [$feature]),
            'not_ours'    => $query->whereRaw('COALESCE(seo_tracked_keywords.serp_features, \'[]\')::jsonb @> ?::jsonb', [$feature])
                ->whereRaw('NOT (COALESCE(seo_tracked_keywords.serp_features_owned, \'[]\')::jsonb @> ?::jsonb)', [$feature]),
            default       => $query->whereRaw('COALESCE(seo_tracked_keywords.serp_features, \'[]\')::jsonb @> ?::jsonb', [$feature]),
        };
    }

    /**
     * @param  array<int, int|string>  $tags  tag ids, and `none` for keywords without a tag
     */
    private function filterTags(Builder $query, array $tags, bool $exclude): void
    {
        $tagIds      = array_values(array_map('intval', array_filter($tags, 'is_numeric')));
        $withoutTags = in_array('none', $tags, true);

        if ($tagIds === [] && !$withoutTags) {
            return;
        }

        $tagged = fn (array $tagIds) => fn (QueryBuilder $subQuery) => $subQuery->selectRaw('1')
            ->from('seo_keyword_tag_keyword')
            ->join('seo_keyword_tags', 'seo_keyword_tags.id', '=', 'seo_keyword_tag_keyword.tag_id')
            ->whereColumn('seo_keyword_tags.shop_id', 'seo_tracked_keywords.shop_id')
            ->whereColumn('seo_keyword_tag_keyword.keyword', 'seo_tracked_keywords.keyword')
            ->when($tagIds !== [], fn (QueryBuilder $subQuery) => $subQuery->whereIn('seo_keyword_tag_keyword.tag_id', $tagIds));

        $query->where(function (Builder $query) use ($tagged, $tagIds, $withoutTags, $exclude) {
            if ($tagIds !== []) {
                $exclude ? $query->whereNotExists($tagged($tagIds)) : $query->orWhereExists($tagged($tagIds));
            }

            if ($withoutTags) {
                $exclude ? $query->whereExists($tagged([])) : $query->orWhereNotExists($tagged([]));
            }
        });
    }

    private function filterRange(Builder $query, string $column, mixed $min, mixed $max, bool $unknownOnly): void
    {
        if ($unknownOnly) {
            $query->whereNull($column);

            return;
        }

        if (is_numeric($min)) {
            $query->where($column, '>=', (int) $min);
        }

        if (is_numeric($max)) {
            $query->where($column, '<=', (int) $max);
        }
    }

    /**
     * @param  array<int, mixed>  $rules  each `{type: include|exclude, match: contains|exact|begins|ends, value}`
     */
    private function filterKeywordRules(Builder $query, array $rules, bool $anyRule): void
    {
        $rules = array_values(array_filter($rules, fn ($rule) => is_array($rule) && trim($this->string($rule['value'] ?? '')) !== ''));

        if ($rules === []) {
            return;
        }

        $query->where(function (Builder $query) use ($rules, $anyRule) {
            foreach ($rules as $rule) {
                $value   = mb_strtolower(trim($this->string($rule['value'])));
                $escaped = addcslashes($value, '%_\\');
                $exclude = ($rule['type'] ?? 'include') === 'exclude';

                [$operator, $pattern] = match ($rule['match'] ?? 'contains') {
                    'exact'  => ['=', $value],
                    'begins' => ['ilike', $escaped.'%'],
                    'ends'   => ['ilike', '%'.$escaped],
                    default  => ['ilike', '%'.$escaped.'%'],
                };

                $condition = fn (Builder $query) => $exclude
                    ? $query->whereNot('seo_tracked_keywords.keyword', $operator, $pattern)
                    : $query->where('seo_tracked_keywords.keyword', $operator, $pattern);

                $anyRule ? $query->orWhere($condition) : $query->where($condition);
            }
        });
    }
}

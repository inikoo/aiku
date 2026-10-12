<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Models\Web;

use App\Enums\Web\Seo\SeoContentBriefStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A writer's brief for one keyword in one market: what the pages in Google's top 10 cover (`pages`,
 * `stats`), the questions and related searches Google shows, our own page for the keyword if we have
 * one, and the brief the AI gateway wrote from them.
 *
 * @property int $id
 * @property int $shop_id
 * @property string $keyword
 * @property string $country_code
 * @property string $language_code
 * @property SeoContentBriefStateEnum $state
 * @property int|null $requested_by_user_id
 * @property array<int, array<string, mixed>>|null $pages
 * @property array<int, string>|null $questions
 * @property array<int, string>|null $related_searches
 * @property array<string, mixed>|null $stats
 * @property array<string, mixed>|null $our_page
 * @property array<string, mixed>|null $brief
 * @property string|null $model
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Shop $shop
 * @property-read User|null $requestedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoContentBrief newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoContentBrief newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoContentBrief query()
 * @mixin \Eloquent
 */
class SeoContentBrief extends Model
{
    public const int STALE_MINUTES = 10;

    protected $guarded = [];

    protected $attributes = [
        'state' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'state'            => SeoContentBriefStateEnum::class,
            'pages'            => 'array',
            'questions'        => 'array',
            'related_searches' => 'array',
            'stats'            => 'array',
            'our_page'         => 'array',
            'brief'            => 'array',
            'finished_at'      => 'datetime',
        ];
    }

    /**
     * A brief still pending after `STALE_MINUTES` lost its job and can be asked for again.
     */
    public function isBeingWritten(): bool
    {
        return $this->state === SeoContentBriefStateEnum::PENDING && $this->created_at?->gte(now()->subMinutes(self::STALE_MINUTES));
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}

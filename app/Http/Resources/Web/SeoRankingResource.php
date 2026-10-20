<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Http\Resources\Web;

use App\Enums\Web\Seo\SeoKeywordDeviceEnum;
use App\Enums\Web\Seo\SeoKeywordFrequencyEnum;
use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $keyword
 * @property string $country_code
 * @property SeoKeywordDeviceEnum $device
 * @property SeoKeywordFrequencyEnum $frequency
 * @property \Illuminate\Support\Carbon|null $last_checked_at
 * @property int|null $position
 * @property int|null $previous_position
 * @property \Illuminate\Support\Carbon|null $previous_checked_at
 * @property string|null $ranking_url
 * @property array|null $serp_features
 * @property bool $in_ai_overview
 * @property string|null $pending_task_id
 * @property int|null $avg_monthly_searches
 * @property string|null $intent
 * @property int|null $keyword_difficulty
 * @property array|null $serp_features_owned
 * @property array|null $tags
 * @property float|null $search_console_position
 * @property array|null $competitor_positions
 * @property bool|null $is_watched
 * @property string|null $alert
 * @property \Illuminate\Support\Collection|null $other_countries
 * @property array<int, int>|null $check_ids
 */
class SeoRankingResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        $otherCountries = collect($this->other_countries ?? []);

        return [
            'id'                      => $this->id,
            'keyword'                 => $this->keyword,
            'country_code'            => $this->country_code,
            'device'                  => $this->device->value,
            'frequency'               => $this->frequency->value,
            'last_checked_at'         => $this->last_checked_at?->toDateString(),
            'is_pending'              => $this->pending_task_id !== null,
            'can_check_now'           => $this->pending_task_id === null && !$this->last_checked_at?->isToday(),
            'position'                => $this->position,
            'previous_position'       => $this->previous_position,
            'has_previous_check'      => $this->previous_checked_at !== null,
            'ranking_url'             => $this->ranking_url,
            'serp_features'           => $this->serp_features ?? [],
            'in_ai_overview'          => $this->in_ai_overview,
            'avg_monthly_searches'    => $this->avg_monthly_searches !== null ? (int) $this->avg_monthly_searches : null,
            'intent'                  => $this->intent,
            'keyword_difficulty'      => $this->keyword_difficulty !== null ? (int) $this->keyword_difficulty : null,
            'serp_features_owned'     => $this->serp_features_owned ?? [],
            'tags'                    => $this->tags ?? [],
            'search_console_position' => $this->search_console_position !== null ? (float) $this->search_console_position : null,
            'competitor_positions'    => $this->competitor_positions ?? [],
            'is_watched'              => (bool) $this->is_watched,
            'alert'                   => $this->alert,
            'history_route'           => ['name' => 'grp.json.seo_tracked_keyword.history', 'parameters' => [$this->id]],
            'watch_route'             => ['name' => 'grp.models.seo_tracked_keyword.watch', 'parameters' => [$this->id]],
            'check_ids'               => $this->check_ids ?? [$this->id],
            'can_check_any'           => ($this->pending_task_id === null && !$this->last_checked_at?->isToday())
                || $otherCountries->contains(fn ($other) => $other->pending_task_id === null && !$other->last_checked_at?->isToday()),
            'other_countries'         => $otherCountries->map(fn ($other) => (new self($other))->toArray($request))->values()->all(),
        ];
    }
}

<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Marketing;

use App\Actions\CRM\SocialMedia\GetSocialMediaPerformance;
use App\Actions\OrgAction;
use App\Enums\CRM\SocialMedia\SocialMediaPlatformEnum;
use App\Enums\CRM\SocialMedia\SocialMediaPostTypeEnum;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportSocialMediaPosts extends OrgAction
{
    use WithSocialMediaScope;

    public function htmlResponse(ActionRequest $request): StreamedResponse
    {
        $query    = GetSocialMediaPerformance::make()->exportQuery($this->parent, $this->periodFrom, $this->periodTo, $this->filters($request));
        $filename = 'social-media-posts-'.($this->periodFrom?->toDateString() ?? 'all').'-'.($this->periodTo?->toDateString() ?? now()->toDateString()).'.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                __('Published'), __('Platform'), __('Account'), __('Shop'), __('Country'), __('Format'), __('Description'), __('Link'),
                __('Reach'), __('Impressions'), __('Reactions'), __('Comments'), __('Shares'), __('Saves'), __('Link clicks'), __('Follows'),
                __('Engagements'), __('Engagement rate %'),
            ]);

            foreach ($query->cursor() as $post) {
                fputcsv($output, [
                    Carbon::parse($post->published_at)->toDateTimeString(),
                    SocialMediaPlatformEnum::labels()[$post->platform] ?? $post->platform,
                    $post->account_name,
                    $post->shop_name,
                    $post->country_code,
                    SocialMediaPostTypeEnum::labels()[$post->type] ?? $post->type,
                    $post->description,
                    $post->permalink,
                    $post->reach,
                    $post->impressions,
                    $post->reactions,
                    $post->comments,
                    $post->shares,
                    $post->saves,
                    $post->link_clicks,
                    $post->follows,
                    $post->engagements,
                    $post->reach > 0 ? round($post->engagements / $post->reach * 100, 2) : null,
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}

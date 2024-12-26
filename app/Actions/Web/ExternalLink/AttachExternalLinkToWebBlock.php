<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 22-10-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\ExternalLink;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Models\Web\ExternalLink;
use App\Models\Web\WebBlock;
use App\Models\Web\Webpage;
use Illuminate\Support\Arr;

class AttachExternalLinkToWebBlock extends OrgAction
{
    use HasWebAuthorisation;

    public function handle(Webpage $webpage, WebBlock $webBlock, ExternalLink $externalLink, array $modelData): void
    {
        $webBlock->externalLinks()->attach(
            $externalLink->id,
            [
                'group_id'        => $externalLink->group_id,
                'organisation_id' => $webpage->organisation_id,
                'webpage_id'      => $webpage->id,
                'website_id'      => $webpage->website_id,
                'show'            => Arr::get($modelData, 'show', true)
            ]
        );


        //todo convert following lines in ExternalLinkHydrateWebsites,ExternalLinkHydrateWebpages,ExternalLinkHydrateWebBlocks

        $externalLink->updateQuietly([
            'number_websites_shown'    => $externalLink->websites()->wherePivot('show', true)->count(),
            'number_websites_hidden'   => $externalLink->websites()->wherePivot('show', false)->count(),
            'number_webpages_shown'    => $externalLink->webpages()->wherePivot('show', true)->count(),
            'number_webpages_hidden'   => $externalLink->webpages()->wherePivot('show', false)->count(),
            'number_web_blocks_shown'  => $externalLink->webBlocks()->wherePivot('show', true)->count(),
            'number_web_blocks_hidden' => $externalLink->webBlocks()->wherePivot('show', false)->count(),
        ]);
    }

    public function rules(): array
    {
        return [
            'show' => ['required', 'boolean']
        ];
    }


    public function action(Webpage $webpage, WebBlock $webBlock, ExternalLink $externalLink, array $modelData): void
    {
        $this->asAction = true;

        $this->initialisationFromGroup($webpage->group, $modelData);

        $this->handle($webpage, $webBlock, $externalLink, $this->validatedData);
    }
}

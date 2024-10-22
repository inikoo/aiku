<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 22-10-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\ExternalLink;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Models\SysAdmin\Group;
use App\Models\Web\ExternalLink;
use App\Models\Web\Webpage;
use App\Rules\AlphaDashSlash;

class StoreExternalLink extends OrgAction
{
    use HasWebAuthorisation;


    private Webpage $webpage;

    public function handle(Group $group, array $modelData): ExternalLink
    {
        if ($externalLink = $group->externalLinks()->where('url', $modelData['url'])->first()) {
            return $externalLink;
        }

        /** @var ExternalLink $externalLink */
        $externalLink = $group->externalLinks()->create($modelData);

        //todo move this code to AttachExternalLinkToWebBlock
        //        foreach ($webpage->webBlocks as $webBlock) {
        //            if ($webBlock->id == Arr::get($modelData, 'web_block_id')) {
        //                $webBlock->externalLinks()->attach($externalLink->id, [
        //                    'group_id' => $webpage->group_id,
        //                    'organisation_id' => $webpage->organisation_id,
        //                    'webpage_id'    => $webpage->id,
        //                    'website_id'    => $webpage->website_id,
        //                    'show' => $webBlock->pivot->show,
        //                ]);
        //            }
        //        }

        //todo create action to check external link status and call it here
        // CheckExternalLinkStatus::make()->action($externalLink);


        return $externalLink;
    }

    public function rules(): array
    {
        return [
            'url' => [
                'sometimes',
                'required',
                'ascii',
                'lowercase',
                'max:255',
                new AlphaDashSlash(),
            ]
        ];
    }


    public function action(Group $group, array $modelData, int $hydratorsDelay = 0, bool $strict = true): ExternalLink
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromGroup($group, $modelData);

        return $this->handle($group, $this->validatedData);
    }
}

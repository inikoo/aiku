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
use App\Models\Web\Webpage;
use Illuminate\Support\Arr;

class AttachExternalLinkToWebBlock extends OrgAction
{
    use HasWebAuthorisation;

    public function handle(Webpage $webpage, array $modelData)
    {
        $webBlockId = Arr::get($modelData, 'web_block_id');
        if ($webBlockId) {
            foreach ($webpage->webBlocks as $webBlock) {
                if ($webBlock->id == $webBlockId) {
                    $webBlock->externalLinks()->attach($modelData['external_link_id'], [
                        'group_id' => $webpage->group_id,
                        'organisation_id' => $webpage->organisation_id,
                        'webpage_id'    => $webpage->id,
                        'website_id'    => $webpage->website_id,
                        'show' => $webBlock->pivot->show,
                    ]);
                }
            }
        } else {

        }
    }

    public function rules(): array
    {
        return [
            'external_link_id' => [
                'required',
                'exists:external_links,id'
            ],
            'web_block_id' => [
                'sometimes',
                'exists:model_has_web_blocks,web_block_id'
            ]
        ];
    }


    public function action(Webpage $webpage, array $modelData, int $hydratorsDelay = 0, bool $strict = true)
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromGroup($webpage->group, $modelData);

        $this->handle($webpage, $this->validatedData);
    }
}

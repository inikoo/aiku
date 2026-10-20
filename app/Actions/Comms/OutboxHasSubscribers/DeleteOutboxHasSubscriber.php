<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 05-03-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Comms\OutboxHasSubscribers;

use App\Actions\OrgAction;
// use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateLocations;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Comms\Outbox;
use App\Models\Comms\OutBoxHasSubscriber;
use Lorisleiva\Actions\ActionRequest;
use App\Actions\Traits\Authorisations\WithCommsEditAuthorisation;

class DeleteOutboxHasSubscriber extends OrgAction
{
    use WithNoStrictRules;
    use WithCommsEditAuthorisation;

    /**
     * @throws \Throwable
     */
    public function handle(OutBoxHasSubscriber $outBoxHasSubscriber)
    {
        $outBoxHasSubscriber->delete();
    }

    public function asController(Outbox $outbox, OutBoxHasSubscriber $outBoxHasSubscriber, ActionRequest $request): void
    {
        abort_unless($outBoxHasSubscriber->outbox_id === $outbox->id, 404);
        $this->initialisationFromShop($outbox->shop, $request);

        $this->handle($outBoxHasSubscriber);
    }

}

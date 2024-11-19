<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox;

use App\Actions\Comms\Outbox\Hydrators\OutboxHydrateEmails;
use App\Actions\Comms\Outbox\Hydrators\OutboxHydrateMailshots;
use App\Actions\HydrateModel;
use App\Models\Comms\Outbox;
use Illuminate\Support\Collection;

class HydrateOutbox extends HydrateModel
{
    public function handle(Outbox $outbox): void
    {
        OutboxHydrateMailshots::run($outbox);
        OutboxHydrateEmails::run($outbox);

    }

    public string $commandSignature = 'hydrate:outboxes {slugs?*}';

    protected function getModel(string $slug): Outbox
    {
        return Outbox::firstWhere($slug);
    }

    protected function getAllModels(): Collection
    {
        return Outbox::get();
    }

}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Nov 2023 21:14:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Outbox;

use App\Actions\HydrateModel;
use App\Actions\Mail\Outbox\Hydrators\OutboxHydrateEmails;
use App\Actions\Mail\Outbox\Hydrators\OutboxHydrateMailshots;
use App\Models\Mail\Outbox;
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

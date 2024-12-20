<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Mailshot;

use App\Actions\Comms\Mailshot\Hydrators\MailshotHydrateDispatchedEmails;
use App\Actions\HydrateModel;
use App\Models\Comms\Mailshot;
use Illuminate\Support\Collection;

class HydrateMailshot extends HydrateModel
{
    public string $commandSignature = 'hydrate:mailshots {organisations?*} {--slugs}';

    public function handle(Mailshot $mailshot): void
    {
        MailshotHydrateDispatchedEmails::run($mailshot);
    }

    protected function getModel(string $slug): Mailshot
    {
        return Mailshot::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Mailshot::get();
    }

}

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
use Illuminate\Console\Command;

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

    public function asCommand(Command $command): int
    {
        $command->info('Hydrating Mailshots');
        $count = Mailshot::count();
        $bar   = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();
        Mailshot::chunk(1000, function (\Illuminate\Database\Eloquent\Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });
        $bar->finish();
        $command->info("");

        return 0;
    }

}

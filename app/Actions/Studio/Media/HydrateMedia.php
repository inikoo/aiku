<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Feb 2023 13:01:38 Malaysia Time, Ubud
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Studio\Media;

use App\Actions\HydrateModel;
use App\Actions\Studio\Media\Hydrators\MediaHydrateMultiplicity;
use App\Actions\Studio\Media\Hydrators\MediaHydrateUsage;
use App\Models\Studio\Media;
use Illuminate\Console\Command;

class HydrateMedia extends HydrateModel
{
    public string $commandSignature = 'media:hydrate {--id=}';

    public function handle(Media $media): void
    {
        MediaHydrateUsage::run($media);
        MediaHydrateMultiplicity::run($media);
    }

    public function asCommand(Command $command): int
    {
        $exitCode = 0;
        if ($command->option('id')) {
            $media = Media::find($command->option('id'));
            $this->handle($media);
        } else {
            $command->withProgressBar(Media::all(), function ($media) {
                if ($media) {
                    $this->handle($media);
                }
            });
            $command->info("");
        }

        return $exitCode;
    }
}

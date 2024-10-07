<?php

namespace App\Actions\Web\Webpage;

use App\Actions\OrgAction;
use App\Models\Web\Webpage;
use Exception;

class FetchWebpageWeblocks extends OrgAction
{
    public string $commandSignature = 'fetch:web-blocks {webpage}';

    public function handle(Webpage $webpage)
    {
        dd($webpage->migration_data);
    }

    public function action(Webpage $webpage)
    {
        $this->initialisation($webpage->organisation, []);

        return $this->handle($webpage);
    }

    public function asCommand($command): int
    {
        try {
            $webpage = Webpage::where('slug', $command->argument('webpage'))->firstOrFail();
        } catch (Exception) {
            $command->error('Webpage not found');
            exit;
        }

        $this->handle($webpage);

        return 0;
    }
}

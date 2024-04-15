<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 Apr 2024 15:11:45 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Wowsbar;

use App\Actions\Helpers\Fetch\StoreFetch;
use App\Actions\Helpers\Fetch\UpdateFetch;
use App\Actions\SourceFetch\FetchAction;
use App\Enums\Helpers\Fetch\FetchTypeEnum;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Console\Command;

class FetchWowsbarAction extends FetchAction
{
    use WithWowsbarOrganisationsArgument;


    protected ?Shop $shop;
    protected array $with;
    protected bool $onlyNew  = false;
    protected bool $fetchAll = false;


    public function processOrganisation(Command $command, Organisation $organisation): int
    {

        try {
            $this->organisationSource = $this->getOrganisationSource($organisation);
        } catch (Exception $exception) {
            $command->error($exception->getMessage());
            return 1;
        }
        $this->organisationSource->initialisation($organisation, $command->option('db_suffix') ?? '');

        $this->organisationSource->fetch = StoreFetch::run(
            [
                'type' => $this->getFetchType(),
                'data' => [
                    'command'   => $command->getName(),
                    'arguments' => $command->arguments(),
                    'options'   => $command->options(),
                ]
            ]
        );




        $command->info('');

        if ($command->option('source_id')) {
            $this->handle($this->organisationSource, $command->option('source_id'));
            UpdateFetch::run($this->organisationSource->fetch, ['number_items' => 1]);
        } else {
            $numberItems = $this->count() ?? 0;
            UpdateFetch::run($this->organisationSource->fetch, ['number_items' => $numberItems]);
            if (!$command->option('quiet') and !$command->getOutput()->isDebug()) {
                $info = '✊ '.$command->getName().' '.$organisation->slug;
                if ($this->shop) {
                    $info .= ' shop:'.$this->shop->slug;
                }
                $command->line($info);
                $this->progressBar = $command->getOutput()->createProgressBar($this->count() ?? 0);
                $this->progressBar->setFormat('debug');
                $this->progressBar->start();
            } else {
                $command->line('Steps '.number_format($this->count()));
            }

            $this->fetchAll($this->organisationSource, $command);
            $this->progressBar?->finish();
        }
        UpdateFetch::run($this->organisationSource->fetch, ['finished_at' => now()]);

        return 0;
    }

    private function getFetchType(): ?FetchTypeEnum
    {
        return null;
    }



}

<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 31 Dec 2024 00:37:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Hydrators;

use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithHydrateCommand
{
    use AsAction;

    private string $model;

    protected function getOrganisationsIds(Command $command): array
    {
        return Organisation::query()->whereIn('type', [OrganisationTypeEnum::SHOP->value, OrganisationTypeEnum::DIGITAL_AGENCY->value])
            ->when($command->argument('organisations'), function ($query) use ($command) {
                $query->whereIn('slug', $command->argument('organisations'));
            })
            ->get()->pluck('id')->toArray();
    }

    public function asCommand(Command $command): int
    {
        $command->info($command->getName());

        $tableName = (new $this->model())->getTable();

        $query = DB::table($tableName)->select('id')->orderBy('id');

        if ($command->hasOption('shop') && $command->option('shop')) {

            $shop = Shop::where('slug', $command->option('shop'))->first();
            if ($shop) {
                $query->where('shop_id', $shop->id);
            }
        }

        if ($command->hasOption('slug') && $command->option('slug')) {
            $query->where('slug', $command->option('slug'));
        }
        if ($command->hasOption('organisations') && $command->argument('organisations')) {
            $this->getOrganisationsIds($command);
            $query->whereIn('organisation_id', $this->getOrganisationsIds($command));
        }

        $count = $query->count();

        $bar = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();

        $query->chunk(1000, function (Collection $modelsData) use ($bar) {
            foreach ($modelsData as $modelId) {
                $instance = (new $this->model())->withTrashed()->find($modelId->id);
                //  if ($instance) { // deleted instances will return null
                $this->handle($instance);
                //  }
                $bar->advance();
            }
        });

        $bar->finish();
        $command->info("");

        return 0;
    }

}

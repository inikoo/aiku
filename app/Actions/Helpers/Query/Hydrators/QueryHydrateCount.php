<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 01:36:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Query\Hydrators;

use App\Actions\Helpers\Query\GetQueryEloquentQueryBuilder;
use App\Models\Helpers\Query;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class QueryHydrateCount
{
    use AsAction;

    private bool $asAction = false;


    public function handle(Query $query)
    {
        $queryBuilder = GetQueryEloquentQueryBuilder::run($query);

        //print($queryBuilder->toSql()."\n");

        $numberItems = $queryBuilder->count();

        $query->update(
            [
                'number_items' => $numberItems,
                'counted_at'   => now()

            ]
        );

        return $numberItems;
    }

    public function byModelType(string $modelType): void
    {
        foreach (Query::where('model_type', $modelType)->get() as $query) {
            $this->handle($query);
        }
    }

    public string $commandSignature = 'query:count {queries?*}';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        if (!$command->argument('queries')) {
            $queries = Query::all();
        } else {
            $queries = Query::query()
                ->when($command->argument('queries'), function ($query) use ($command) {
                    $query->whereIn('slug', $command->argument('queries'));
                })
                ->cursor();
        }

        $exitCode = 0;
        foreach ($queries as $query) {
            $this->handle($query);
            $command->line("Query $query->name count: $query->number_items");
        }


        return $exitCode;
    }

}

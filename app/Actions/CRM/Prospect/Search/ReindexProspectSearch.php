<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 22:06:41 Central Indonesia Time, Bali Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect\Search;

use App\Actions\HydrateModel;
use App\Models\CRM\Prospect;
use Illuminate\Support\Collection;

class ReindexProspectSearch extends HydrateModel
{
    public string $commandSignature = 'prospect:search {organisations?*} {--s|slugs=}';


    public function handle(Prospect $prospect): void
    {
        ProspectRecordSearch::run($prospect);
    }


    protected function getModel(string $slug): Prospect
    {
        return Prospect::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Prospect::withTrashed()->get();
    }
}

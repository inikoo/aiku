<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 18-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Web\Webpage\Search;

use App\Actions\HydrateModel;
use App\Models\Web\Webpage;
use Illuminate\Support\Collection;

class ReindexWebpageSearch extends HydrateModel
{
    public string $commandSignature = 'search:webpages {organisations?*} {--s|slugs=} ';


    public function handle(Webpage $webpage): void
    {
        WebpageRecordSearch::run($webpage);
    }

    protected function getModel(string $slug): Webpage
    {
        return Webpage::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Webpage::withTrashed()->get();
    }
}

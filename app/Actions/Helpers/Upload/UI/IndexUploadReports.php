<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Upload\UI;

use App\Http\Resources\Helpers\UploadReportResource;
use App\Models\Helpers\Upload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\Concerns\AsAction;

class IndexUploadReports
{
    use AsAction;

    /**
     * @param Model|Collection<int, Model> $parent several parents of one type, for a page that rolls them up
     */
    public function handle(Model|Collection $parent, string $model, string $prefix, int $perPage = 10): AnonymousResourceCollection
    {
        $parents = $parent instanceof Model ? collect([$parent]) : $parent;

        return UploadReportResource::collection(
            Upload::query()
                ->with(['user', 'parent'])
                ->where('parent_type', $parents->first()?->getMorphClass())
                ->whereIn('parent_id', $parents->map->getKey())
                ->where('model', $model)
                ->orderByDesc('id')
                ->paginate(perPage: $perPage, pageName: $prefix.'Page')
                ->withQueryString()
        );
    }
}

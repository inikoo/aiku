<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 16 Aug 2023 08:09:28 Malaysia Time, Pantai Lembeng, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Uploads;

use App\Http\Resources\Helpers\UploadsResource;
use App\Models\Helpers\Upload;
use App\Models\Leads\Prospect;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class HistoryUploads
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(string $class): array|Collection
    {
        $upload = Upload::whereType($class)->orderBy('id', 'DESC')->limit(4)->get();
        return $upload->reverse();
    }

    public function jsonResponse(Collection $collection): JsonResource
    {
        return UploadsResource::collection($collection);
    }

    public function inProspect(): array|Collection
    {
        return $this->handle(class_basename(Prospect::class));
    }
}

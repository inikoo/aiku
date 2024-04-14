<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Mail\Outbox\UpdateOutbox;
use App\Models\Mail\Outbox;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchAuroraOutboxes extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:outboxes {organisations?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Outbox
    {
        if ($shopData = $organisationSource->fetchOutbox($organisationSourceId)) {
            if ($outbox = Outbox::where('source_id', $shopData['outbox']['source_id'])
                ->first()) {
                $outbox = UpdateOutbox::run(
                    shop: $outbox,
                    modelData: $shopData['outbox']
                );
                DB::connection('aurora')->table('Email Campaign Type Dimension')
                    ->where('Email Campaign Type Key', $outbox->source_id)
                    ->update(['aiku_id' => $outbox->id]);
                return $outbox;
            }
        }

        return null;
    }


    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Email Campaign Type Dimension')
            ->select('Email Campaign Type Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Email Campaign Type Dimension')->count();
    }
}

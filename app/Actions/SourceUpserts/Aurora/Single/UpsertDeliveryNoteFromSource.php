<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 19:59:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */


/** @noinspection PhpUnused */

namespace App\Actions\SourceUpserts\Aurora\Single;

use App\Actions\Delivery\DeliveryNote\StoreDeliveryNote;
use App\Actions\Delivery\DeliveryNote\UpdateDeliveryNote;
use App\Models\Delivery\DeliveryNote;
use App\Services\Organisation\SourceOrganisationService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property \App\Models\Organisations\Organisation $organisation
 * @property \App\Models\Delivery\DeliveryNote $deliveryNote
 */
class UpsertDeliveryNoteFromSource
{
    use AsAction;
    use WithSingleFromSourceCommand;

    public string $commandSignature = 'source-update:delivery_note {organisation_code} {organisation_source_id}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?DeliveryNote
    {
        if ($deliveryNoteData = $organisationSource->fetchDeliveryNote($organisation_source_id)) {
            if ($deliveryNote = DeliveryNote::where('organisation_source_id', $deliveryNoteData['delivery_note']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {

                $res = UpdateDeliveryNote::run(
                    deliveryNote:    $deliveryNote,
                    modelData:       $deliveryNoteData['delivery_note'],
                    deliveryAddress: $deliveryNoteData['delivery_address']
                );
            } else {
                $res = StoreDeliveryNote::run(
                    order:           $deliveryNoteData['order'],
                    deliveryAddress: $deliveryNoteData['delivery_address'],
                    modelData:       $deliveryNoteData['delivery_note']
                );
            }


            return $res->model;
        }


        return null;
    }


}

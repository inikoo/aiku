<?php

/*
 * Author: eka yudinata (https://github.com/ekayudinata)
 * Created: Tuesday, 3 Feb 2026 15:55:10 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, eka yudinata
 */

namespace App\Actions\Comms\Mailshot;

use App\Actions\Comms\Email\StoreEmail;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithCatalogueAuthorisation;
use App\Models\Comms\Mailshot;
use Illuminate\Support\Facades\DB;

class CloneMailshotForSecondWave extends OrgAction
{
    use WithCatalogueAuthorisation;

    /**
     * @throws \Throwable
     */
    public function handle(Mailshot $parentMailshot): Mailshot
    {
        $dataModel = [
            'date' => $parentMailshot->date,
            'group_id' => $parentMailshot->group_id,
            'organisation_id' => $parentMailshot->organisation_id,
            'shop_id' => $parentMailshot->shop_id,
            'type' => $parentMailshot->type,
            'subject' => SyncMailshotSecondWaveSubject::subjectFor($parentMailshot),
            'name' => $parentMailshot->name,
            'state' => $parentMailshot->state,
            'is_second_wave' => true,
            'ready_at' => $parentMailshot->ready_at,
            'outbox_id' => $parentMailshot->outbox_id,
            'recipients_recipe' => $parentMailshot->recipients_recipe,
            'preview_text' => $parentMailshot->preview_text,
        ];

        $secondWaveMailshot = DB::transaction(function () use ($parentMailshot, $dataModel) {
            /** @var Mailshot $newMailshot */
            $newMailshot = $parentMailshot->secondWave()->create($dataModel);

            $newMailshot->stats()->create();

            $this->copyEmail($parentMailshot, $newMailshot);

            return $newMailshot;
        });

        return $secondWaveMailshot;
    }

    public function copyEmail(Mailshot $sourceMailshot, Mailshot $targetMailshot): void
    {
        $sourceEmail    = $sourceMailshot->email;
        $sourceSnapshot = $sourceEmail->designSnapshot();

        StoreEmail::make()->action(
            $targetMailshot,
            null,
            modelData: [
                'subject' => $targetMailshot->subject,
                'builder' => $sourceEmail->builder,
                'layout' => $sourceSnapshot?->layout,
                'compiled_layout' => $sourceSnapshot?->compiled_layout,
                'snapshot_state' => $sourceSnapshot?->state,
                'snapshot_published_at' => $sourceSnapshot?->published_at,
                'snapshot_recyclable' => $sourceSnapshot?->recyclable ?? false,
                'snapshot_first_commit' => $sourceSnapshot?->first_commit ?? true,
            ],
            strict: false
        );
    }

    public function action(Mailshot $mailshot): Mailshot
    {
        return $this->handle($mailshot);
    }
}

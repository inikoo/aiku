<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailTemplate;

use App\Actions\Helpers\Snapshot\StoreEmailTemplateSnapshot;
use App\Actions\OrgAction;
use App\Models\Comms\EmailTemplate;
use App\Models\Comms\Outbox;
use Arr;

class StoreEmailTemplate extends OrgAction
{
    public function handle(Outbox $outbox, array $modelData): EmailTemplate
    {
        $layout = Arr::get($modelData, 'layout', []);
        data_forget($modelData, 'layout');

        data_set($modelData, 'group_id', $outbox->group_id);
        data_set($modelData, 'organisation_id', $outbox->organisation_id);


        /** @var EmailTemplate $emailTemplate */
        $emailTemplate = $outbox->emailTemplate()->create($modelData);

        $snapshot = StoreEmailTemplateSnapshot::run(
            $emailTemplate,
            [
                'layout' => $layout
            ],
        );

        $emailTemplate->update(
            [
                'unpublished_snapshot_id' => $snapshot->id,

            ]
        );

        return $emailTemplate;
    }

    public function rules(): array
    {
        return [
            'layout'  => ['sometimes', 'array']
        ];
    }


    public function action(Outbox $outbox, array $modelData): EmailTemplate
    {
        $this->asAction = true;
        $this->initialisation($outbox->organisation, $modelData);

        return $this->handle($outbox, $this->validatedData);
    }
}

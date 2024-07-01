<?php

namespace App\Actions\Mail\EmailTemplate;

use App\Actions\Helpers\Snapshot\StoreEmailTemplateSnapshot;
use App\Actions\OrgAction;
use App\Models\Mail\EmailTemplate;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\Organisation;
use Illuminate\Validation\Rules\Enum;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreEmailTemplate extends OrgAction
{

    use AsAction;

    public function handle(Outbox $outbox, array $modelData): EmailTemplate
    {
        data_set($modelData, 'group_id', $outbox->group_id);
        data_set($modelData, 'organisation_id', $outbox->organisation_id);
        if (isset($outbox->shop_id)) {
            data_set($modelData, 'shop_id', $outbox->shop_id);
        }

        data_set($modelData, 'parent_type', get_class($outbox));
        data_set($modelData, 'parent_id', $outbox->id);
        
        $emailTemplate = $outbox->emailTemplates()->create($modelData);

        $snapshot = StoreEmailTemplateSnapshot::run(
            $emailTemplate,
            [
                'layout' => []
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
            'name'  => ['required', 'max:255']
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

    }

    public function action(Organisation $organisation, Outbox $outbox, array $modelData): EmailTemplate
    {
        $this->asAction = true;
        $this->initialisation($organisation, $modelData);

        return $this->handle($outbox, $this->validatedData);
    }
}
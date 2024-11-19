<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailTemplate;

use App\Actions\Helpers\Deployment\StoreDeployment;
use App\Actions\Helpers\Snapshot\StoreEmailTemplateSnapshot;
use App\Actions\Helpers\Snapshot\UpdateSnapshot;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Comms\EmailTemplate\EmailTemplateStateEnum as EmailTemplateEmailTemplateStateEnum;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Models\Comms\EmailTemplate;
use App\Models\Helpers\Snapshot;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use OwenIt\Auditing\Resolvers\UserResolver;

class PublishEmailTemplate extends OrgAction
{
    use WithActionUpdate;
    use HasWebAuthorisation;

    public function handle(EmailTemplate $emailtemplate, array $modelData): EmailTemplate
    {
        /** @var User $user */
        $user = UserResolver::resolve();

        if ($user) {
            data_set($modelData, 'publisher_type', class_basename($user), overwrite: false);
            data_set($modelData, 'publisher_id', $user->id, overwrite: false);
        }

        $firstCommit = false;
        if ($emailtemplate->state == EmailTemplateEmailTemplateStateEnum::IN_PROCESS) {
            $firstCommit = true;
        }

        foreach ($emailtemplate->snapshots()->where('state', SnapshotStateEnum::LIVE)->get() as $liveSnapshot) {
            UpdateSnapshot::run($liveSnapshot, [
                'state'           => SnapshotStateEnum::HISTORIC,
                'published_until' => now()
            ]);
        }

        $currentUnpublishedLayout = $emailtemplate->unpublishedSnapshot->layout;


        /** @var Snapshot $snapshot */
        $snapshot = StoreEmailTemplateSnapshot::run(
            $emailtemplate,
            [
                'state'          => SnapshotStateEnum::LIVE,
                'published_at'   => now(),
                'layout'         => $currentUnpublishedLayout,
                'first_commit'   => $firstCommit,
                'comment'        => Arr::get($modelData, 'comment'),
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
            ]
        );

        $deployment = StoreDeployment::run(
            $emailtemplate,
            [
                'snapshot_id'    => $snapshot->id,
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
            ]
        );

        $emailtemplate->stats()->update([
            'last_deployed_at' => $deployment->date
        ]);

        $updateData = [
            'live_snapshot_id'   => $snapshot->id,
            'published_layout'   => $snapshot->layout,
            'published_checksum' => md5(json_encode($snapshot->layout)),
            'state'              => EmailTemplateEmailTemplateStateEnum::LIVE,
            'is_dirty'           => false,
        ];

        if ($emailtemplate->state == EmailTemplateEmailTemplateStateEnum::IN_PROCESS) {
            $updateData['live_at'] = now();
        }

        $emailtemplate->update($updateData);

        return $emailtemplate;
    }

    public function asController(EmailTemplate $emailtemplate, ActionRequest $request): EmailTemplate
    {
        $this->scope = $emailtemplate->website->shop;
        $this->initialisationFromShop($emailtemplate->website->shop, $request);


        return $this->handle($emailtemplate, $this->validatedData);
    }

    public function rules(): array
    {
        $rules = [
            'comment' => ['sometimes', 'required', 'string', 'max:1024'],
        ];

        if (!$this->strict) {
            $rules['publisher_type'] = ['sometimes', Rule::in(['User'])];
            $rules['publisher_id']   = ['sometimes', 'required', 'integer'];
        }

        return $rules;
    }


    public function jsonResponse(EmailTemplate $emailtemplate): string
    {
        return "🚀";
    }

    public function action(EmailTemplate $emailtemplate, array $modelData, bool $strict = true): EmailTemplate
    {
        $this->strict   = $strict;
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($emailtemplate, $validatedData);
    }
}

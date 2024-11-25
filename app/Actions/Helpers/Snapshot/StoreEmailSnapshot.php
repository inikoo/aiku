<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Oct 2023 08:55:05 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Snapshot;

use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Helpers\Snapshot\SnapshotBuilderEnum;
use App\Enums\Helpers\Snapshot\SnapshotScopeEnum;
use App\Models\Comms\Email;
use App\Models\Helpers\Snapshot;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreEmailSnapshot extends OrgAction
{
    use WithNoStrictRules;


    public function handle(Email $email, array $modelData): Snapshot
    {
        data_set($modelData, 'scope', SnapshotScopeEnum::EMAIL);
        data_set(
            $modelData,
            'checksum',
            md5(
                json_encode(
                    Arr::get($modelData, 'layout')
                )
            )
        );

        /** @var Snapshot $snapshot */
        $snapshot = $email->snapshots()->create($modelData);
        $snapshot->stats()->create();

        return $snapshot;
    }

    public function authorize(): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }


    public function rules(): array
    {
        $rules = [
            'builder' => ['required', Rule::enum(SnapshotBuilderEnum::class)],
            'layout' => ['required', 'array'],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }


}

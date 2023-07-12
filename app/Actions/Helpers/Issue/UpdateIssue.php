<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Issue;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Helpers\IssueResource;
use App\Models\Helpers\Issue;
use Lorisleiva\Actions\ActionRequest;

class UpdateIssue
{
    use WithActionUpdate;

    public function handle(Issue $issue, array $modelData): Issue
    {
        return $this->update($issue, $modelData, ['data']);
    }

    //    public function authorize(ActionRequest $request): bool
    //    {
    //        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    //    }

    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'required'],
        ];
    }


    public function asController(Issue $issue, ActionRequest $request): Issue
    {
        $request->validate();
        return $this->handle($issue, $request->all());
    }


    public function jsonResponse(Issue $issue): IssueResource
    {
        return new IssueResource($issue);
    }
}

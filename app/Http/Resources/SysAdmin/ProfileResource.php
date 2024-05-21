<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 21:56:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SysAdmin;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\HumanResources\Timesheet\UI\IndexTimesheets;
use App\Actions\SysAdmin\UserRequest\IndexUserRequestLogs;
use App\Actions\UI\Profile\GetProfileShowcase;
use App\Enums\UI\SysAdmin\ProfileTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\HumanResources\TimesheetsResource;
use App\Models\SysAdmin\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ProfileResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var User $user */
        $user = $this;

        return [
            'showcase'   => GetProfileShowcase::run($user->resource),
            'timesheets' => TimesheetsResource::collection(IndexTimesheets::run($user->resource->parent, ProfileTabsEnum::TIMESHEETS->value)),
            'history'    => HistoryResource::collection(IndexHistory::run($user->resource, ProfileTabsEnum::HISTORY->value)),
            'visit_logs' => UserRequestLogsResource::collection(IndexUserRequestLogs::run())
        ];
    }
}

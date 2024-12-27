<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 27-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\CRM\WebUser;

use App\Enums\Helpers\Audit\AuditEventEnum;
use App\Models\CRM\WebUser;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class WebUserHydrateAudits
{
    use AsAction;

    private WebUser $webUser;

    public function __construct(WebUser $webUser)
    {
        $this->webUser = $webUser;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->webUser->id))->dontRelease()];
    }

    public function handle(WebUser $webUser): void
    {
        $baseQuery = DB::table('audits')
            ->where('website_id', $webUser->website_id)
            ->where('customer_id', $webUser->customer_id)
            ->where('user_type', 'WebUser');

        $stats = [
            'number_audits' => $baseQuery->count(),
        ];

        foreach (AuditEventEnum::cases() as $case) {
            if ($case == AuditEventEnum::MIGRATED) {
                continue;
            }

            $stats["number_audits_event_{$case->snake()}"] = $baseQuery->clone()
            ->where('event', $case)
            ->count();
        }

        $webUser->stats->update($stats);
    }

    public string $commandSignature = 'hydrate:web_user_audits';

    public function asCommand($command): void
    {
        $webusers = WebUser::all();

        foreach ($webusers as $webuser) {
            $this->handle($webuser);
        }
    }

}

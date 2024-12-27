<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SysAdmin\User\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Helpers\Audit\AuditEventEnum;
use App\Models\SysAdmin\User;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UserHydrateAudits
{
    use AsAction;
    use WithEnumStats;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->user->id))->dontRelease()];
    }

    public function handle(User $user): void
    {
        $queryBase = DB::table('audits')
            ->where('user_id', $user->id)
            ->where('user_type', 'User');

        $stats = [
            'number_audits' => $queryBase->count(),
        ];

        foreach (AuditEventEnum::cases() as $case) {
            if ($case == AuditEventEnum::MIGRATED) {
                continue;
            }

            $stats["number_audits_event_{$case->snake()}"] = $queryBase->clone()
            ->where('event', $case)
            ->count();
        }

        $user->stats->update($stats);
    }

    public string $commandSignature = 'hydrate:user_audits';

    public function asCommand($command): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $this->handle($user);
        }
    }

}

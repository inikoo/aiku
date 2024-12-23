<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 19-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\CRM\Prospect\ProspectContactedStateEnum;
use App\Enums\CRM\Prospect\ProspectFailStatusEnum;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Enums\CRM\Prospect\ProspectSuccessStatusEnum;
use App\Enums\Miscellaneous\GenderEnum;
use App\Models\CRM\Prospect;
use App\Models\SysAdmin\Group;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateProspects
{
    use AsAction;
    use WithEnumStats;
    private Group $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->group->id))->dontRelease()];
    }

    public function handle(Group $group): void
    {
        // $totalProspectCustomerMale = $group->prospects()
        // ->whereHas('customer', function ($query) {
        //     $query->where('gender', 'male');
        // })
        // ->count();
        // dd($totalProspectCustomerMale->toArray());
        // foreach(GenderEnum::values() as $gender) {

        // }


        $stats = [
            'number_prospects' => $group->prospects()->count(),
            'number_prospects_dont_contact_me' => $group->prospects()->where('dont_contact_me', true)->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'prospects',
                field: 'state',
                enum: ProspectStateEnum::class,
                models: Prospect::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        // $stats = array_merge(
        //     $stats,
        //     $this->getEnumStats(
        //         model: 'prospects',
        //         field: 'gender',
        //         enum: GenderEnum::class,
        //         models: Prospect::class,
        //         where: function ($q) use ($group) {
        //             $q->where('group_id', $group->id);
        //         }
        //     )
        // );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'prospects',
                field: 'contacted_state',
                enum: ProspectContactedStateEnum::class,
                models: Prospect::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'prospects',
                field: 'fail_status',
                enum: ProspectFailStatusEnum::class,
                models: Prospect::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'prospects',
                field: 'success_status',
                enum: ProspectSuccessStatusEnum::class,
                models: Prospect::class,
                where: function ($q) use ($group) {
                    $q->where('group_id', $group->id);
                }
            )
        );

        $group->crmStats()->update($stats);
    }

    public string $commandSignature = 'hydrate:group_prospects';

    public function asCommand($command): void
    {
        $group = Group::first();
        $this->handle($group);
    }
}

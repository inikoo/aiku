<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 15-10-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Web\Redirect\RedirectTypeEnum;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Redirect;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateRedirects
{
    use AsAction;
    use WithEnumStats;
    private Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->organisation->id))->dontRelease()];
    }

    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_redirects' => $organisation->redirects()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'redirects',
                field: 'type',
                enum: RedirectTypeEnum::class,
                models: Redirect::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $organisation->webStats()->update($stats);
    }
}

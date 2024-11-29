<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 30 Nov 2024 00:22:22 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\PostRoom\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Comms\PostRoom;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PostRoomHydrateOrgPostRooms
{
    use AsAction;
    use WithEnumStats;

    private PostRoom $postRoom;

    public function __construct(PostRoom $postRoom)
    {
        $this->postRoom = $postRoom;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->postRoom->id))->dontRelease()];
    }

    public function handle(PostRoom $postRoom): void
    {
        $stats = [
            'number_org_post_rooms' => $postRoom->orgPostRooms()->count(),
        ];

        $postRoom->stats()->update($stats);
    }

}

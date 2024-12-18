<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 17-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Comms\PostRoom\UI;

use App\Http\Resources\Mail\PostRoomResource;
use App\Models\Comms\PostRoom;
use Lorisleiva\Actions\Concerns\AsObject;

class GetPostRoomShowcase
{
    use AsObject;

    public function handle(PostRoom $postRoom): array
    {
        return [
            'postRoom' => PostRoomResource::make($postRoom),
            'stats'   => $postRoom->stats
        ];
    }
}

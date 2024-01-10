<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 07 Dec 2023 14:06:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Events;

use App\Models\SysAdmin\Group;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BroadcastNotification implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public array $data;
    public Group $group;

    public function __construct(Group $group, string $title, string $text)
    {
        $this->group = $group;
        $this->data  = [
            'title' => $title,
            'text'  => $text
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("grp.".$this->group->id.".general")
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification';
    }
}

<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 15:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace Database\Factories\Chat;

use App\Enums\CRM\Livechat\ChatAiRunKindEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatAiRunFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kind'       => ChatAiRunKindEnum::HOURLY,
            'teams'      => [],
            'model'      => 'claude-opus-5-5',
            'started_at' => now(),
            'counts'     => [],
        ];
    }
}

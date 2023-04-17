<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:33 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Issue;

use App\Models\Helpers\Issue;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreIssue
{
    use AsAction;

    public function handle($modelData): Issue
    {
        $issue = Issue::create($modelData);
//        $issue->stats()->create();

        return $issue;
    }
}

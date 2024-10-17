<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:34:03 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Media;

use App\Models\Helpers\Media;
use Exception;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteAttachment
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(Media $attachment): void
    {
        try {
            $attachment->delete();
        } catch (Exception $e) {
            throw new Exception('Error deleting attachment: '.$e->getMessage());
        }
    }


    /**
     * @throws \Exception
     */
    protected function action(Media $attachment): void
    {
        $this->handle($attachment);
    }



}

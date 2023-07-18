<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Firebase;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsObject;
use Kreait\Firebase\Database\Reference;

class DeleteAllFirebase
{
    use AsObject;
    use AsAction;

    public string $commandSignature = 'firebase:flush';

    public string $commandDescription = 'Delete all data from Firebase Realtime Database';

    /**
     * @throws \Kreait\Firebase\Exception\DatabaseException
     */
    public function handle(): void
    {
        // Get the root reference of the Firebase Realtime Database
        $rootReference = Firebase::database()->getReference();

        // Delete all data recursively starting from the root reference
        $this->deleteData($rootReference);

        echo "🧼 All data deleted from Firebase Realtime Database \n";
    }

    /**
     * @throws \Kreait\Firebase\Exception\DatabaseException
     */
    private function deleteData(Reference $reference): void
    {
        $reference->remove();
    }

    /**
     * @throws \Kreait\Firebase\Exception\DatabaseException
     */
    public function asCommand(): void
    {
        $this->handle();
    }
}

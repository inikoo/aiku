<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 08 Jun 2023 14:34:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Imports;

use App\Actions\Auth\User\StoreUserHistories;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UserHistoryImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection): void
    {
        $i = 0;
        foreach ($collection as $row) {
            if ($i > 0) {
                $userHistories = [
                    'user_id'    => $row[0],
                    'event'      => $row[1],
                    'old_values' => $row[2],
                    'new_values' => $row[3],
                    'url'        => $row[4],
                    'ip_address' => $row[5],
                    'user_agent' => $row[6],
                ];

                StoreUserHistories::run($userHistories);
            }
            $i++;
        }
    }
}

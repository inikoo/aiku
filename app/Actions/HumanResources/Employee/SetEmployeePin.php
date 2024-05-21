<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 20 May 2024 16:19:39 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use App\Models\HumanResources\Employee;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SetEmployeePin
{
    use AsAction;

    public function handle(Employee $employee): string
    {
        return $this->setPin($employee);
    }

    public function setPin($employee, $try = 1): bool
    {
        try {
            $letters    = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'X', 'Y', 'Z');
            $emojis     = array('🌴', '😀', '👽', '🍄', '👻', '👍🏼', '🚀', '🦄', '🐋', '☘️');
            $numbers    = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');



            $pin=$employee->organisation_id.':'.
                $letters[array_rand($letters, 1)].$letters[array_rand($letters, 1)].
                $emojis[array_rand($emojis, 1)].$emojis[array_rand($emojis, 1)].
                $numbers[array_rand($numbers, 1)].$numbers[array_rand($numbers, 1)];

            $employee->update(
                [
                    'pin' => $pin
                ]
            );


            return true;
        } catch (Exception) {
            if ($try < 100) {
                $this->setPin($employee, $try + 1);
            } else {
                return false;
            }
        }

        return false;

    }

    public string $commandSignature = 'employee:set_pin {employee}';

    public function asCommand(Command $command): int
    {
        try {
            $employee = Employee::where('slug', $command->argument('employee'))->firstOrFail();
        } catch (Exception) {
            $command->error('Employee not found');
            return 1;
        }

        if($this->handle($employee)) {
            $command->info('Pin set for '.$employee->alias.' pin: '.$employee->pin);
            return 0;
        } else {
            $command->error('Pin could not be set for '.$employee->alias);
            return 1;
        }

    }

}

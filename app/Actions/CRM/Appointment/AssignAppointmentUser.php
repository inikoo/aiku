<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Appointment;

use App\Models\CRM\Appointment;
use App\Models\SysAdmin\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Throwable;

class AssignAppointmentUser
{
    use AsAction;
    use WithAttributes;

    public string $commandSignature = 'appointment:assign {appointment} {user}';

    public function handle(Appointment $appointment, User $user): Model
    {
        $appointment->update([
            'organisation_user_id' => $user->id
        ]);

        // TODO: Maybe will need notification later for user who assigned

        return $appointment;
    }

    /**
     * @throws Throwable
     */
    public function asController(Appointment $appointment, User $user): Model
    {
        return $this->handle($appointment, $user);
    }

    public function asCommand(Command $command): int
    {
        $user        = User::where('username', $command->argument('user'))->first();
        $appointment = Appointment::findOrFail($command->argument('appointment'));

        $this->handle($appointment, $user);

        echo "🫡 Success assign appointment to $user->username \n";

        return 1;
    }
}

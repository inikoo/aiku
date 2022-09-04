<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Jan 2022 21:09:21 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Hydrators;

use App\Models\Organisations\Organisation;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;


class HydrateModel
{
    use AsAction;

    protected function getModel(int $id):?Model{
       return null;
    }

    protected function getAllModels():Collection{
       return new Collection();
    }


    public function asCommand(Command $command): void
    {

        $organisation = Organisation::where('code', $command->argument('organisation_code'))->first();
        if (!$organisation) {
            $command->error('Organisation not found');
            return;
        }

        if($command->argument('id')){
            $model=$this->getModel($command->argument('id'));
            if($model){
                $this->handle($model);
                $command->info('Done!');
            }
        }else{
            $this->loopAll($command);
        }


    }


    protected function loopAll(Command $command): void
    {
        $command->withProgressBar($this->getAllModels(), function ($model) {
            if($model){
                $this->handle($model);
            }
        });
        $command->info("");
    }

}



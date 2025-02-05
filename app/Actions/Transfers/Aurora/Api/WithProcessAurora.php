<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Feb 2025 13:10:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora\Api;

use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

trait WithProcessAurora
{
    public function rules(): array
    {
        return [
            'id'   => ['required', 'integer'],
            'with' => ['sometimes', 'string'],
            'bg'   => ['sometimes', 'present'],
        ];
    }


    /**
     * @throws \Exception
     */
    public function asController(Organisation $organisation, ActionRequest $request): array
    {
        $this->initialisation($organisation, $request);
        $validatedData = $this->validatedData;

        if ($with = Arr::get($validatedData, 'with', '')) {
            $with = explode(',', $with);
        }


        if (Arr::has($validatedData, 'bg')) {
            (new $this->fetcher())::dispatch($organisation->id, Arr::get($validatedData, 'id'), $with);

            return [
                'status' => 'ok',
                'type'   => 'background'
            ];
        } else {

            $model = (new $this->fetcher())::make()->action($organisation->id, Arr::get($validatedData, 'id'), $with);

            if ($model) {
                return [
                    'status' => 'ok',
                    'type'   => 'foreground',
                    'id'     => $model->id
                ];
            } else {
                return [
                    'status' => 'error',
                    'type'   => 'foreground'
                ];
            }
        }
    }
}

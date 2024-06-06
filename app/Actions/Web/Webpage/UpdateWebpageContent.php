<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Jun 2024 10:22:50 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Web\WebpageResource;
use App\Models\Web\Webpage;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateWebpageContent extends OrgAction
{
    use AsAction;
    use WithActionUpdate;

    public function handle(Webpage $webpage, array $data): Webpage
    {
        return $this->update($webpage, $data);
    }

    public function rules(): array
    {
        return [
            'compiled_layout' => ['required', 'array'],
        ];
    }

    public function asController(Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->initialisationFromShop($webpage->website->shop, $request);

        return $this->handle($webpage, $this->validatedData);
    }

    public function jsonResponse(Webpage $webpage): WebpageResource
    {
        return WebpageResource::make($webpage);
    }


}

<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:30:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\WithActionUpdate;
use App\Models\Market\Shop;
use App\Models\Web\Website;
use Lorisleiva\Actions\ActionRequest;

class UpdateWebsite
{
    use WithActionUpdate;


    public function handle(Website $website, array $modelData): Website
    {
        return $this->update($website, $modelData, ['data', 'settings']);
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("websites.edit");
    }


    public function rules(): array
    {
        return [
            'domain' => ['sometimes','required'],
            'code'   => ['sometimes','required', 'unique:tenant.websites','max:8'],
            'name'   => ['sometimes','required']
        ];
    }

    public function asController(Website $website, ActionRequest $request): Website
    {
        $request->validate();

        return $this->handle($website, $request->all());
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, Website $website, ActionRequest $request): Website
    {
        $request->validate();

        return $this->handle($website, $request->all());
    }
}

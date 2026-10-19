<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Marketing;

use App\Actions\Traits\Dashboards\WithMarketingPeriod;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

trait WithSocialMediaScope
{
    use WithMarketingPeriod;

    protected Organisation|Group $parent;

    public function authorize(ActionRequest $request): bool
    {
        $user = $request->user();

        foreach ($this->parent->shops()->pluck('id') as $shopId) {
            if ($user->authTo("marketing.$shopId.view")) {
                return true;
            }
        }

        return false;
    }

    public function asController(Organisation $organisation, ActionRequest $request): ActionRequest
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);
        $this->setMarketingPeriod($request->user()->settings);

        return $request;
    }

    public function inGroup(ActionRequest $request): ActionRequest
    {
        $this->parent = group();
        $this->initialisationFromGroup(group(), $request);
        $this->setMarketingPeriod($request->user()->settings);

        return $request;
    }

    /**
     * @return array{shop: string|null, country: string|null, platform: string|null}
     */
    public function filters(ActionRequest $request): array
    {
        return [
            'shop'     => $request->string('shop')->toString() ?: null,
            'country'  => $request->string('country')->toString() ?: null,
            'platform' => $request->string('platform')->toString() ?: null,
        ];
    }
}

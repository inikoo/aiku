<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\GoogleTagManager;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithShopPpcPermissions;
use App\Services\GoogleTagManager\GoogleTagManagerClient;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;

class ConnectGoogleTagManager extends OrgAction
{
    use WithShopPpcPermissions;

    public function authorize(ActionRequest $request): bool
    {
        return $this->isGroupAdmin($request->user());
    }

    public function asController(ActionRequest $request): Response
    {
        $this->initialisationFromGroup(group(), $request);

        return Inertia::location(GoogleTagManagerClient::authUrl(group()));
    }
}

<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 03 Jun 2024 23:34:44 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use Lorisleiva\Actions\ActionRequest;

trait WithWebAuthorisation
{
    use WithShopPpcPermissions;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $routeName = $request->route()->getName();
        $user = $request->user();

        // Helper for org supervisor routes
        $orgSupervisorRoutes = [
            'grp.org.websites.',
            'grp.models.org.product.',
            'grp.models.webpage.'
        ];

        foreach ($orgSupervisorRoutes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                $this->canEdit = $user->authTo("org-supervisor.{$this->organisation->id}");
                $this->isSupervisor = $this->canEdit;

                return $user->authTo([
                    "websites-view.{$this->organisation->id}",
                    "group-webmaster.view"
                ]);
            }
        }

        if (str_starts_with($routeName, 'grp.overview.')) {
            return $user->authTo("group-overview");
        }

        if (str_starts_with($routeName, 'grp.org.shops.show.seo.')) {
            $this->canEdit      = $this->canEditSeo($user, $this->shop);
            $this->isSupervisor = $this->canEdit;

            return $this->canViewSeo($user, $this->shop);
        }

        if (str_starts_with($routeName, 'grp.org.shops.show.web.')) {
            $this->canEdit = $user->authTo([
                "web.{$this->shop->id}.edit",
                "group-webmaster.edit"
            ]);
            $this->isSupervisor = $user->authTo([
                "supervisor-web.{$this->shop->id}",
                "group-webmaster.edit"
            ]);
            return $user->authTo([
                "websites-view.{$this->organisation?->id}",
                "web.{$this->shop->id}",
                "web.{$this->shop->id}.view",
                "group-webmaster.view"
            ]);
        }

        // TODO RAUL PLEASE REVIEW
        if (str_starts_with($routeName, 'grp.models.product.')) {
            $this->canEdit = $user->authTo([
                "web.{$this->shop->id}.edit",
                "group-webmaster.edit"
            ]);
            $this->isSupervisor = $user->authTo([
                "supervisor-web.{$this->shop->id}",
                "group-webmaster.edit"
            ]);
            return $user->authTo([
                "websites-view.{$this->organisation?->id}",
                "web.{$this->shop->id}",
                "web.{$this->shop->id}.view",
                "group-webmaster.view"
            ]);
        }

        if (str_starts_with($routeName, 'grp.org.fulfilments.show.web.')) {
            $this->canEdit = $user->authTo([
                "fulfilment-shop.{$this->fulfilment->id}.edit",
                "group-webmaster.edit"
            ]);
            $this->isSupervisor = $user->authTo([
                "supervisor-fulfilment-shop.{$this->fulfilment->id}",
                "group-webmaster.edit"
            ]);
            return $user->authTo([
                "websites-view.{$this->organisation?->id}",
                "fulfilment-shop.{$this->fulfilment->id}",
                "fulfilment-shop.{$this->fulfilment->id}.view",
                "group-webmaster.view"
            ]);
        }

        if (str_starts_with($routeName, 'grp.json.') || str_starts_with($routeName, 'grp.websites.')) {
            $permissions = [
                "websites-view.{$this->shop->organisation_id}",
                "web.{$this->shop->id}",
                "web.{$this->shop->id}.view",
                "web.{$this->shop->id}.edit",
                "supervisor-web.{$this->shop->id}",
                "group-webmaster.view",
                "group-webmaster.edit",
            ];
            if ($this->shop->fulfilment) {
                $permissions[] = "fulfilment-shop.{$this->shop->fulfilment->id}";
                $permissions[] = "fulfilment-shop.{$this->shop->fulfilment->id}.view";
                $permissions[] = "fulfilment-shop.{$this->shop->fulfilment->id}.edit";
                $permissions[] = "supervisor-fulfilment-shop.{$this->shop->fulfilment->id}";
            }

            return $user->authTo($permissions);
        }

        return false;
    }
}

<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Marketing;

use App\Actions\CRM\SocialMedia\GetSocialMediaPerformance;
use App\Actions\CRM\SocialMedia\SyncShopFromMeta;
use App\Actions\Helpers\Upload\UI\IndexUploadReports;
use App\Actions\OrgAction;
use App\Actions\UI\Dashboards\ShowGroupDashboard;
use App\Models\Catalogue\Shop;
use App\Models\CRM\SocialMediaPost;
use App\Models\SysAdmin\Group;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowSocialMediaDashboard extends OrgAction
{
    use WithSocialMediaScope;

    public const string UPLOADS = 'uploads';

    public function htmlResponse(ActionRequest $request): Response
    {
        $isGroup = $this->parent instanceof Group;
        $title   = __('Social media');
        $user    = $request->user();
        $shops   = $this->parent->shops()->with('organisation')->orderBy('name')->get();

        return Inertia::render(
            'Org/Marketing/SocialMediaDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs($isGroup),
                'title'        => $title,
                'pageHead'     => [
                    'icon'  => [
                        'icon'  => ['fal', 'fa-hashtag'],
                        'title' => $title,
                    ],
                    'model' => $isGroup ? __('Marketing').' ('.__('all organisations').')' : __('Marketing').' ('.$this->parent->name.')',
                    'title' => $title,
                ],
                'intervals'    => $this->intervalsProp($user->settings),
                'settings'     => [],
                'upload_shops' => $shops
                    ->filter(fn (Shop $shop) => $user->authTo("marketing.$shop->id.edit"))
                    ->map(fn (Shop $shop) => [
                        'id'    => $shop->id,
                        'name'  => $shop->name,
                        'route' => ['name' => 'grp.models.shop.social_media.upload', 'parameters' => ['shop' => $shop->id]],
                    ])
                    ->values(),
                'meta_sync'    => fn () => $shops
                    ->filter(fn (Shop $shop) => $user->authTo("marketing.$shop->id.edit"))
                    ->map(fn (Shop $shop) => [
                        'id'         => $shop->id,
                        'slug'       => $shop->slug,
                        'name'       => $shop->name,
                        'route'      => ['name' => 'grp.models.shop.social_media.sync', 'parameters' => ['shop' => $shop->id]],
                        'edit_route' => ['name' => 'grp.org.shops.show.settings.edit', 'parameters' => ['organisation' => $shop->organisation->slug, 'shop' => $shop->slug]],
                        ...SyncShopFromMeta::status($shop),
                    ])
                    ->values(),
                'export_route' => $isGroup
                    ? ['name' => 'grp.marketing.social_media.export', 'parameters' => []]
                    : ['name' => 'grp.org.marketing.social_media.export', 'parameters' => ['organisation' => $this->parent->slug]],
                'performance'  => Inertia::defer(fn () => array_merge(
                    GetSocialMediaPerformance::run($this->parent, $this->periodFrom, $this->periodTo, $this->filters($request)),
                    $this->periodLabels(),
                )),
                self::UPLOADS  => Inertia::defer(fn () => IndexUploadReports::run(
                    parent: $shops,
                    model: class_basename(SocialMediaPost::class),
                    prefix: self::UPLOADS
                ), 'uploads'),
            ]
        );
    }

    public function getBreadcrumbs(bool $isGroup): array
    {
        return array_merge(
            ShowGroupDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $isGroup
                            ? ['name' => 'grp.marketing.dashboard']
                            : ['name' => 'grp.org.marketing.dashboard', 'parameters' => [$this->parent->slug]],
                        'label' => $isGroup ? __('Marketing').' ('.__('group').')' : __('Marketing'),
                        'icon'  => 'fal fa-bullhorn',
                    ],
                ],
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $isGroup
                            ? ['name' => 'grp.marketing.social_media.dashboard']
                            : ['name' => 'grp.org.marketing.social_media.dashboard', 'parameters' => [$this->parent->slug]],
                        'label' => __('Social media'),
                        'icon'  => 'fal fa-hashtag',
                    ],
                ],
            ]
        );
    }
}

<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailTemplate;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateBanners;
use App\Actions\Helpers\Deployment\StoreDeployment;
use App\Actions\Helpers\Snapshot\StoreEmailTemplateSnapshot;
use App\Actions\Helpers\Snapshot\UpdateSnapshot;
use App\Actions\Portfolio\Banner\Hydrators\BannerHydrateUniversalSearch;
use App\Actions\Portfolio\Banner\UpdateBannerImage;
use App\Actions\Portfolio\PortfolioWebsite\Hydrators\PortfolioWebsiteHydrateBanners;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Helpers\Snapshot\SnapshotStateEnum;
use App\Enums\Portfolio\Banner\BannerStateEnum;
use App\Models\Helpers\Snapshot;
use App\Models\Mail\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class SetEmailTemplateAsPublish
{
    use WithActionUpdate;

    public bool $isAction = false;

    public function handle(EmailTemplate $emailTemplate, array $modelData): EmailTemplate
    {
        $firstCommit = false;

        if ($emailTemplate->state == BannerStateEnum::UNPUBLISHED) {
            $firstCommit = true;
        }

        foreach ($emailTemplate->snapshots()->where('state', SnapshotStateEnum::LIVE)->get() as $liveSnapshot) {
            UpdateSnapshot::run($liveSnapshot, [
                'state'           => SnapshotStateEnum::HISTORIC,
                'published_until' => now()
            ]);
        }

        $layout = Arr::pull($modelData, 'compiled');

        /** @var Snapshot $snapshot */
        $snapshot = StoreEmailTemplateSnapshot::run(
            $emailTemplate,
            [
                'state'          => SnapshotStateEnum::LIVE,
                'published_at'   => now(),
                'layout'         => $layout,
                'first_commit'   => $firstCommit,
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
                'comment'        => Arr::get($modelData, 'comment')
            ]
        );

        StoreDeployment::run(
            $emailTemplate,
            [
                'snapshot_id'    => $snapshot->id,
                'publisher_id'   => Arr::get($modelData, 'publisher_id'),
                'publisher_type' => Arr::get($modelData, 'publisher_type'),
            ]
        );

        $compiledLayout = $snapshot->compiledLayout();

        $updateData = [
            'live_snapshot_id' => $snapshot->id,
            'compiled_layout'  => $compiledLayout,
            'state'            => BannerStateEnum::LIVE,
        ];

        if ($emailTemplate->state == BannerStateEnum::UNPUBLISHED) {
            $updateData['live_at'] = now();
            $updateData['date']    = now();
        }

        $emailTemplate->update($updateData);
        BannerHydrateUniversalSearch::dispatch($emailTemplate);
        CustomerHydrateBanners::dispatch(customer());

        foreach ($emailTemplate->portfolioWebsites as $portfolioWebsite) {
            PortfolioWebsiteHydrateBanners::run($portfolioWebsite);
        }

        UpdateBannerImage::dispatch($emailTemplate);

        return $emailTemplate;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->isAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.prospects.edit");
    }

    public function rules(): array
    {
        return [
            'publisher_id' => ['sometimes', 'exists:organisation_users,id'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $request->merge(
            [
                'publisher_id' => $request->user()->id,
            ]
        );
    }

    public function asController(EmailTemplate $emailTemplate, ActionRequest $request): EmailTemplate
    {

        $request->validate();
        return $this->handle($emailTemplate, $request->validated());
    }

    public function action(EmailTemplate $emailTemplate, $modelData): EmailTemplate
    {
        $this->isAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($emailTemplate, $validatedData);
    }

    public function htmlResponse(EmailTemplate $emailTemplate, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('org.crm.shop.prospects.mailshots.show', [
            $emailTemplate->scope->slug,
            $emailTemplate->slug
        ]);
    }
}

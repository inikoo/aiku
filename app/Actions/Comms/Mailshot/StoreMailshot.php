<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Mailshot;

use App\Actions\Comms\Mailshot\UI\HasUIMailshots;
use App\Actions\Comms\Outbox\Hydrators\OutboxHydrateMailshots;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Comms\Mailshot\MailshotStateEnum;
use App\Enums\Comms\Mailshot\MailshotTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Mailshot;
use App\Models\Comms\Outbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreMailshot extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use HasUIMailshots;
    use HasCatalogueAuthorisation;
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(Outbox|Shop $parent, array $modelData): Mailshot
    {
        data_set($modelData, 'date', now(), overwrite: false);
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        if ($parent instanceof Outbox) {
            data_set($modelData, 'shop_id', $parent->shop_id);
        } else {
            data_set($modelData, 'shop_id', $parent->id);
        }


        $mailshot = DB::transaction(function () use ($parent, $modelData) {
            /** @var Mailshot $mailshot */
            $mailshot = $parent->mailshots()->create($modelData);
            $mailshot->stats()->create();

            return $mailshot;
        });

        if ($parent instanceof Outbox) {
            OutboxHydrateMailshots::dispatch($parent)->delay($this->hydratorsDelay);
        }

        return $mailshot;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        //todo
        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
    }

    // public function afterValidator($validator)
    // {
    //     dd($validator);
    // }

    public function rules(): array
    {
        $rules = [
            'subject'           => ['required', 'string', 'max:255'],
            'type'              => ['sometimes', 'required', Rule::enum(MailshotTypeEnum::class)],
            'state'             => ['sometimes', Rule::enum(MailshotStateEnum::class)],
            'recipients_recipe' => ['present', 'array']

        ];

        if (!$this->strict) {
            $rules['date']             = ['nullable', 'date'];
            $rules['ready_at']         = ['nullable', 'date'];
            $rules['scheduled_at']     = ['nullable', 'date'];
            $rules['start_sending_at'] = ['nullable', 'date'];
            $rules['sent_at']          = ['nullable', 'date'];
            $rules['stopped_at']       = ['nullable', 'date'];


            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }


    /**
     * @throws \Throwable
     */
    public function action(Outbox|Shop $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): Mailshot
    {
        if (!$audit) {
            Mailshot::disableAuditing();
        }
        if ($parent instanceof Outbox) {
            $shop = $parent->shop;
        } else {
            $shop = $parent;
        }
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;


        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    /**
     * @throws \Throwable
     */
    public function asController(Shop $shop, ActionRequest $request): Mailshot
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function inOutbox(Outbox $outbox, ActionRequest $request): Mailshot
    {
        $this->initialisationFromShop($outbox->shop, $request);

        return $this->handle($outbox, $this->validatedData);
    }


    public function htmlResponse(Mailshot $mailshot): \Symfony\Component\HttpFoundation\Response
    {
        return Inertia::location(route('grp.org.shops.show.marketing.mailshots.index', [
            'organisation' => $mailshot->shop->organisation->slug,
            'shop'         => $mailshot->shop->slug
        ]));
    }
}

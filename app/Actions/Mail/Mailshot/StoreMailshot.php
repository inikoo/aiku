<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\Mail\Mailshot\UI\HasUIMailshots;
use App\Actions\Mail\Outbox\Hydrators\OutboxHydrateMailshots;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Mail\Mailshot\MailshotTypeEnum;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
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
    public function handle(Outbox $outbox, array $modelData): Mailshot
    {
        $modelData['shop_id'] = $outbox->shop_id;


        $mailshot = DB::transaction(function () use ($outbox, $modelData) {
            /** @var Mailshot $mailshot */
            $mailshot = $outbox->mailshots()->create($modelData);
            $mailshot->stats()->create();

            return $mailshot;
        });


        OutboxHydrateMailshots::dispatch($outbox)->delay($this->hydratorsDelay);

        return $mailshot;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        //todo
        return false;
    }


    public function rules(): array
    {
        $rules = [
            'subject'           => ['required', 'string', 'max:255'],
            'type'              => ['required', new Enum(MailshotTypeEnum::class)],
            'recipients_recipe' => ['present', 'array']

        ];

        if (!$this->strict) {

            $rules = $this->noStrictStoreRules($rules);

        }

        return $rules;
    }


    /**
     * @throws \Throwable
     */
    public function action(Outbox $outbox, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): Mailshot
    {
        if (!$audit) {
            Mailshot::disableAuditing();
        }

        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;


        $this->initialisationFromShop($outbox->shop, $modelData);

        return $this->handle($outbox, $this->validatedData);
    }

    /**
     * @throws \Throwable
     */
    public function asController(Outbox $outbox, ActionRequest $request): Mailshot
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

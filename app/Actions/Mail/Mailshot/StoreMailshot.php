<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\Mail\Mailshot\UI\HasUIMailshots;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Models\Catalogue\Shop;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\Organisation;
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

    public function handle(Outbox|Shop $parent, array $modelData): Mailshot
    {
        if($parent instanceof Outbox) {
            $modelData['shop_id']=$parent->shop_id;
        }

        /** @var Mailshot $mailshot */
        $mailshot = $parent->mailshots()->create($modelData);
        $mailshot->stats()->create();

        return $mailshot;
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Mailshot
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData);
    }

    public function htmlResponse(Mailshot $mailshot): \Symfony\Component\HttpFoundation\Response
    {
        return Inertia::location(route('grp.org.shops.show.marketing.mailshots.index', [
            'organisation' => $mailshot->shop->organisation->slug,
            'shop'         => $mailshot->shop->slug
        ]));
    }

    /*
    public function rules(): array
    {
        return [
            'code'         => ['required', 'unique:mailshots', 'between:2,256', 'alpha_dash'],
            'name'         => ['required', 'max:250', 'string'],
        ];
    }
    */

    public function action(Outbox $outbox, array $modelData): Mailshot
    {
        $this->asAction=true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($outbox, $validatedData);
    }
}

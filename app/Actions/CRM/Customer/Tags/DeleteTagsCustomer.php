<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Tags;

use App\Models\Helpers\Tag;
use App\Models\Catalogue\Shop;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteTagsCustomer
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;
    /**
     * @var \App\Models\Catalogue\Shop
     */
    private Shop $parent;

    public function handle(Tag $tag): void
    {
        $tag->crmStats()->delete();
        $tag->delete();
    }

    public function htmlResponse(): RedirectResponse
    {
        return redirect()->route(
            'grp.org.shops.show.crm.prospects.tags.index',
            $this->parent->slug
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.prospects.edit");
    }

    public function rules(ActionRequest $request): array
    {
        return [
            'tags' => ['nullable', 'array'],
            'type' => ['nullable', 'string']
        ];
    }

    public function asController(Shop $shop, Tag $tag): void
    {
        $this->parent = $shop;
        $this->handle($tag);
    }
}

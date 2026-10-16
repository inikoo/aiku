<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 17:30:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;

trait WithInvoiceCategoryListAuthorisation
{
    /**
     * Null when every shop of the category is visible, otherwise the shops the user can see.
     *
     * @var array<int>|null
     */
    protected ?array $invoiceCategoryViewableShopIds = null;

    /**
     * Accounting sees the whole category; customer services and order staff see the invoices of
     * the shops they work in, as they already can open those invoices one by one.
     */
    protected function authorizeInvoiceCategoryList(User $user, Organisation $organisation): bool
    {
        if ($user->authTo("accounting.$organisation->id.view")) {
            return true;
        }

        $this->invoiceCategoryViewableShopIds = $organisation->shops()->pluck('id')
            ->filter(fn (int $shopId) => $user->authTo(["crm.$shopId.view", "orders.$shopId.view"]))
            ->values()
            ->all();

        return $this->invoiceCategoryViewableShopIds !== [];
    }
}

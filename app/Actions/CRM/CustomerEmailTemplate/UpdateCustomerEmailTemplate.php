<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\CustomerEmailTemplate;

use App\Actions\Chat\ChatSession\StartCustomerEmailChat;
use App\Actions\Chat\WithChatAgentAuthorisation;
use App\Actions\OrgAction;
use App\Models\Catalogue\Shop;
use App\Models\CRM\CustomerEmailTemplate;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\ActionRequest;

class UpdateCustomerEmailTemplate extends OrgAction
{
    use WithChatAgentAuthorisation;

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'body'    => ['required', 'string'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $user = $request->user();

        return $user instanceof User
            && ($this->userCanActOnChatOnShop($user, $this->shop) || StartCustomerEmailChat::userCanEmailCustomersOnShop($user, $this->shop));
    }

    public function handle(CustomerEmailTemplate $customerEmailTemplate, array $modelData): CustomerEmailTemplate
    {
        $customerEmailTemplate->update(array_merge($modelData, [
            'updated_by_user_id' => Auth::id(),
        ]));

        return $customerEmailTemplate;
    }

    public function asController(Shop $shop, CustomerEmailTemplate $customerEmailTemplate, ActionRequest $request): CustomerEmailTemplate
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($customerEmailTemplate, $this->validatedData);
    }

    public function action(CustomerEmailTemplate $customerEmailTemplate, array $modelData): CustomerEmailTemplate
    {
        $this->asAction = true;
        $this->initialisationFromShop($customerEmailTemplate->shop, $modelData);

        return $this->handle($customerEmailTemplate, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}

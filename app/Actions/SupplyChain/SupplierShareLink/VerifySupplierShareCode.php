<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierShareLink;

use App\Models\SupplyChain\SupplierShareLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * A code works once, for ten minutes, and five wrong tries spend it, so a new one has to be sent to the supplier's email.
 */
class VerifySupplierShareCode
{
    use AsAction;

    public const int ATTEMPTS = 5;

    public function handle(SupplierShareLink $link, string $code): bool
    {
        if (!$link->code_hash || !$link->code_expires_at?->isFuture() || $link->code_attempts >= self::ATTEMPTS) {
            return false;
        }

        if (!hash_equals($link->code_hash, SendSupplierShareCode::hashCode($link, $code))) {
            $link->increment('code_attempts');

            return false;
        }

        $link->update(['code_hash' => null, 'code_expires_at' => null, 'code_attempts' => 0]);
        session()->regenerate();
        session()->put($link->sessionKey(), true);

        return true;
    }

    public function rules(): array
    {
        return ['code' => ['required', 'string', 'max:12']];
    }

    public function asController(string $token, ActionRequest $request): RedirectResponse
    {
        $link = SupplierShareLink::findLiveByToken($token);

        if (!$this->handle($link, preg_replace('/\D/', '', $request->validated('code')))) {
            throw ValidationException::withMessages(['code' => __('That code is not right or has expired. Ask for a new one.')]);
        }

        return redirect()->route('grp.supplier_share.show', $token);
    }
}

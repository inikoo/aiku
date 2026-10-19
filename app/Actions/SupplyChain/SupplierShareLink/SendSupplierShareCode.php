<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierShareLink;

use App\Models\SupplyChain\SupplierShareLink;
use App\Notifications\SupplierShareCodeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Sends a six digit code to the email the link was made for. Only that address ever gets it, whatever the visitor types.
 */
class SendSupplierShareCode
{
    use AsAction;

    public const int MINUTES = 10;

    public function handle(SupplierShareLink $link): void
    {
        abort_unless($link->email, 404);

        $code = (string) random_int(100000, 999999);
        $link->update([
            'code_hash'       => self::hashCode($link, $code),
            'code_expires_at' => now()->addMinutes(self::MINUTES),
            'code_attempts'   => 0,
        ]);

        Notification::route('mail', $link->email)->notify(new SupplierShareCodeNotification($code, $link->group->name));
    }

    public static function hashCode(SupplierShareLink $link, string $code): string
    {
        return hash('sha256', $link->id.':'.$code);
    }

    public function asController(string $token): RedirectResponse
    {
        $this->handle(SupplierShareLink::findLiveByToken($token));

        return back()->with('code_sent', true);
    }
}

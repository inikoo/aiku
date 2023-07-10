<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 30 Jun 2023 13:24:46 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Traits;

use App\Models\Notifications\FcmToken;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Fcmable
{
    protected mixed $fcmToken;

    public function currentFcmToken(): string
    {
        return $this->fcmToken;
    }

    public function withFcmToken($fcmToken): static
    {
        $this->fcmToken = $fcmToken;

        return $this;
    }

    public function fcmToken(): MorphOne
    {
        return $this->morphOne(FcmToken::class, 'fcmable');
    }

    public function fcmTokens(): MorphMany
    {
        return $this->morphMany(FcmToken::class, 'fcmable');
    }
}

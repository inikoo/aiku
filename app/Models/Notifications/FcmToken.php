<?php

namespace App\Models\Notifications;

use Eloquent;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * @property string $fcm_token;
 * @mixin Eloquent
 */
class FcmToken extends Model
{
    use UsesTenantConnection;

    protected $guarded = [];

    public static function boot(): void
    {
        parent::boot();

        self::creating(function($model) {
            $parsedUserAgent = (new Browser())->parse(request()->server('HTTP_USER_AGENT'));
            $model->platform = $parsedUserAgent;
        });
    }

    public function getTokenAttribute(): string
    {
        return $this->fcm_token;
    }

    public function fcmable(): MorphTo
    {
        return $this->morphTo();
    }
}

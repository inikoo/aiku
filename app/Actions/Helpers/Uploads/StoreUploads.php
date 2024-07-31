<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 16 Aug 2023 08:09:28 Malaysia Time, Pantai Lembeng, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Uploads;

use App\Models\CRM\WebUser;
use App\Models\Helpers\Upload;
use App\Models\SysAdmin\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreUploads
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle($file, $class): Upload
    {
        /** @var User $user */
        $user     = request()->user();

        if($user instanceof WebUser) {
            $user = null;
        }

        $filename = $file->hashName();
        $type     = class_basename($class);
        $path     = 'excel-uploads/org/' . Str::lower($type);

        Storage::disk('excel-uploads')->put($path, $file);

        return Upload::create([
            'user_id'              => $user?->id,
            'type'                 => $type,
            'original_filename'    => $file->getClientOriginalName(),
            'filename'             => $filename,
            'path'                 => $path,
            'uploaded_at'          => now()
        ]);
    }
}

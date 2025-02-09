<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Vite;

class PrepareViteForNextOperation
{
    public function handle(): void
    {
        Vite::flushPreloadedAssets();

    }
}

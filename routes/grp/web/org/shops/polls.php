<?php
/*
 * author Arya Permana - Kirin
 * created on 23-12-2024-15h-33m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

use App\Actions\CRM\Poll\UI\IndexPolls;
use App\Actions\CRM\Prospect\Mailshots\UI\IndexProspectMailshots;
use App\Actions\CRM\Prospect\Tags\UI\IndexProspectTags;
use App\Actions\CRM\Prospect\UI\CreateProspect;
use App\Actions\CRM\Prospect\UI\IndexProspects;

Route::get('/', IndexPolls::class)->name('index');

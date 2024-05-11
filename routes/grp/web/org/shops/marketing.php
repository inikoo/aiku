<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 11:23:04 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Mail\DispatchedEmail\IndexDispatchedEmails;
use App\Actions\Mail\DispatchedEmail\ShowDispatchedEmail;
use App\Actions\Mail\Mailroom\IndexMailrooms;
use App\Actions\Mail\Mailroom\ShowMailroom;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\Mail\Mailshot\ShowMailshot;
use App\Actions\Mail\Mailshot\UI\CreateMailshot;
use App\Actions\Mail\Outbox\IndexOutboxes;
use App\Actions\Mail\Outbox\ShowOutbox;
use App\Actions\Mail\Outbox\UI\EditOutbox;
use App\Actions\UI\Marketing\MarketingHub;
use Illuminate\Support\Facades\Route;

/*
Route::get('/mailshots/create', CreateMailshot::class)->name('mailshots.create');

Route::get('/', [MarketingHub::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('hub');

Route::get('/mailrooms', [IndexMailrooms::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('mailrooms.index');
Route::get('/mailrooms/{mailroom}', [ShowMailroom::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('mailrooms.show');
Route::get('/mailrooms/{mailroom}/outboxes', [IndexOutboxes::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('mailrooms.show.outboxes.index');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}', [ShowOutbox::class, 'tenant' ? 'inMailroom' : 'inMailroomInShop'])->name('mailrooms.show.outboxes.show');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}/mailshots', [IndexMailshots::class, 'tenant' ? 'inMailroom' : 'inMailroomInShop'])->name('mailrooms.show.outboxes.show.mailshots.index');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}/mailshots/{mailshot}', [ShowMailshot::class, 'tenant' ? 'inMailroom' : 'inMailroomInOutboxInShop'])->name('mailrooms.show.outboxes.show.mailshots.show');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}/mailshots/{mailshot}/dispatched-emails', [IndexDispatchedEmails::class, 'tenant' ? 'inMailroom' : 'inMailroomInOutboxInShop'])->name('mailrooms.show.outboxes.show.mailshots.show.dispatched-emails.index');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}/mailshots/{mailshot}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, 'tenant' ? 'inMailroom' : 'inMailroomInOutboxInMailshotInShop'])->name('mailrooms.show.outboxes.show.mailshots.show.dispatched-emails.show');

Route::get('/mailrooms/{mailroom}/mailshots', [IndexMailshots::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('mailrooms.show.mailshots.index');
Route::get('/mailrooms/{mailroom}/mailshots/{mailshot}', [ShowMailroom::class, $parent == 'tenant' ? 'inOrganisation' : 'inMailroomInShop'])->name('mailrooms.show.mailshots.show');
Route::get('/mailrooms/{mailroom}/mailshots/{mailshot}/dispatched-emails', [IndexDispatchedEmails::class,$parent == 'tenant' ? 'inOrganisation' : 'inMailroomInShop'])->name('mailrooms.show.mailshots.show.dispatched-emails.index');
Route::get('/mailrooms/{mailroom}/mailshots/{mailshot}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, $parent == 'tenant' ? 'inOrganisation' : 'inMailroomInMailshotInShop'])->name('mailrooms.show.mailshots.show.dispatched-emails.show');

Route::get('/mailrooms/{mailroom}/dispatched-emails', [IndexDispatchedEmails::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('mailrooms.show.dispatched-emails.index');
Route::get('/mailrooms/{mailroom}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, $parent == 'tenant' ? 'inOrganisation' : 'inMailroomInShop'])->name('mailrooms.show.dispatched-emails.show');

Route::get('/outboxes', [IndexOutboxes::class,  $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('outboxes.index');
Route::get('/outboxes/{outbox}', [ShowOutbox::class,  $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('outboxes.show');
Route::get('/outboxes/{outbox}/edit', [EditOutbox::class,  $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('outboxes.edit');

Route::get('/outboxes/{outbox}/mailshots', [IndexMailshots::class,  $parent == 'tenant' ? 'inOrganisation' : 'inOutboxInShop'])->name('outboxes.show.mailshots.index');
Route::get('/outboxes/{outbox}/mailshots/{mailshot}', [ShowMailshot::class,  $parent == 'tenant' ? 'inOrganisation' : 'inOutboxInShop'])->name('outboxes.show.mailshots.show');
Route::get('/outboxes/{outbox}/mailshots/{mailshot}/dispatched-emails', [IndexDispatchedEmails::class, $parent == 'tenant' ? 'inOrganisation' : 'inOutboxInShop'])->name('outboxes.show.mailshots.show.dispatched-emails.index');
Route::get('/outboxes/{outbox}/mailshots/{mailshot}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, $parent == 'tenant' ? 'inOrganisation' : 'inOutboxInShop'])->name('outboxes.show.mailshots.show.dispatched-emails.show');

Route::get('/outboxes/{outbox}/dispatched-emails', [IndexDispatchedEmails::class,  $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('outboxes.show.dispatched-emails.index');
Route::get('/outboxes/{outbox}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class,  $parent == 'tenant' ? 'inOrganisation' : 'inOutBoxInShop'])->name('outboxes.show.dispatched-emails.show');


Route::get('/mailshots', [IndexMailshots::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('mailshots.index');

Route::get('/mailshots/{mailshot}', [ShowMailshot::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('mailshots.show');
Route::get('/mailshots/{mailshot}/dispatched-emails', [IndexDispatchedEmails::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('mailshots.show.dispatched-emails.index');
Route::get('/mailshots/{mailshot}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, $parent == 'tenant' ? 'inOrganisation' : 'inMailshotInShop' ])->name('mailshots.show.dispatched-emails.show');

Route::get('/dispatched-emails', [IndexDispatchedEmails::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('dispatched-emails.index');
Route::get('/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, $parent == 'tenant' ? 'inOrganisation' : 'inShop'])->name('dispatched-emails.show');

*/

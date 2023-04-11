<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 17 Mar 2023 10:36:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
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
use App\Actions\UI\Mail\MailHub;
use Illuminate\Support\Facades\Route;

if (empty($parent)) {
    $parent = 'tenant';
}

Route::get('/mailshots/create', CreateMailshot::class)->name('mailshots.create');

Route::get('/', [MailHub::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('dashboard');

Route::get('/mailrooms', [IndexMailrooms::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('mailrooms.index');
Route::get('/mailrooms/{mailroom}', [ShowMailroom::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('mailrooms.show');
Route::get('/mailrooms/{mailroom}/outboxes', [IndexOutboxes::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('mailrooms.show.outboxes.index');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}', [ShowOutbox::class, 'tenant' ? 'inMailroom' : 'inMailroomInShop'])->name('mailrooms.show.outboxes.show');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}/mailshots', [IndexMailshots::class, 'tenant' ? 'inMailroom' : 'inMailroomInShop'])->name('mailrooms.show.outboxes.show.mailshots.index');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}/mailshots/{mailshot}', [ShowMailshot::class, 'tenant' ? 'inMailroom' : 'inMailroomInOutboxInShop'])->name('mailrooms.show.outboxes.show.mailshots.show');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}/mailshots/{mailshot}/dispatched-emails', [IndexDispatchedEmails::class, 'tenant' ? 'inMailroom' : 'inMailroomInOutboxInShop'])->name('mailrooms.show.outboxes.show.mailshots.show.dispatched-emails.index');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}/mailshots/{mailshot}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, 'tenant' ? 'inMailroom' : 'inMailroomInOutboxInMailshotInShop'])->name('mailrooms.show.outboxes.show.mailshots.show.dispatched-emails.show');

Route::get('/mailrooms/{mailroom}/mailshots', [IndexMailshots::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('mailrooms.show.mailshots.index');
Route::get('/mailrooms/{mailroom}/mailshots/{mailshot}', [ShowMailroom::class, $parent == 'tenant' ? 'inTenant' : 'inMailroomInShop'])->name('mailrooms.show.mailshots.show');
Route::get('/mailrooms/{mailroom}/mailshots/{mailshot}/dispatched-emails', [IndexDispatchedEmails::class,$parent == 'tenant' ? 'inTenant' : 'inMailroomInShop'])->name('mailrooms.show.mailshots.show.dispatched-emails.index');
Route::get('/mailrooms/{mailroom}/mailshots/{mailshot}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, $parent == 'tenant' ? 'inTenant' : 'inMailroomInMailshotInShop'])->name('mailrooms.show.mailshots.show.dispatched-emails.show');

Route::get('/mailrooms/{mailroom}/dispatched-emails', [IndexDispatchedEmails::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('mailrooms.show.dispatched-emails.index');
Route::get('/mailrooms/{mailroom}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, $parent == 'tenant' ? 'inTenant' : 'inMailroomInShop'])->name('mailrooms.show.dispatched-emails.show');

Route::get('/outboxes', [IndexOutboxes::class,  $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('outboxes.index');
Route::get('/outboxes/{outbox}', [ShowOutbox::class,  $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('outboxes.show');
Route::get('/outboxes/{outbox}/edit', [EditOutbox::class,  $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('outboxes.edit');

Route::get('/outboxes/{outbox}/mailshots', [IndexMailshots::class,  $parent == 'tenant' ? 'inTenant' : 'inOutboxInShop'])->name('outboxes.show.mailshots.index');
Route::get('/outboxes/{outbox}/mailshots/{mailshot}', [ShowMailshot::class,  $parent == 'tenant' ? 'inTenant' : 'inOutboxInShop'])->name('outboxes.show.mailshots.show');
Route::get('/outboxes/{outbox}/mailshots/{mailshot}/dispatched-emails', [IndexDispatchedEmails::class, $parent == 'tenant' ? 'inTenant' : 'inOutboxInShop'])->name('outboxes.show.mailshots.show.dispatched-emails.index');
Route::get('/outboxes/{outbox}/mailshots/{mailshot}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, $parent == 'tenant' ? 'inTenant' : 'inOutboxInShop'])->name('outboxes.show.mailshots.show.dispatched-emails.show');

Route::get('/outboxes/{outbox}/dispatched-emails', [IndexDispatchedEmails::class, 'inOutbox'])->name('outboxes.show.dispatched-emails.index');
Route::get('/outboxes/{outbox}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, 'inOutbox'])->name('outboxes.show.dispatched-emails.show');


Route::get('/mailshots', [IndexMailshots::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('mailshots.index');

Route::get('/mailshots/{mailshot}', [ShowMailshot::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('mailshots.show');
Route::get('/mailshots/{mailshot}/dispatched-emails', [IndexDispatchedEmails::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('mailshots.show.dispatched-emails.index');
Route::get('/mailshots/{mailshot}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, $parent == 'tenant' ? 'inTenant' : 'inMailshotInShop' ])->name('mailshots.show.dispatched-emails.show');

Route::get('/dispatched-emails', [IndexDispatchedEmails::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('dispatched-emails.index');
Route::get('/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('dispatched-emails.show');

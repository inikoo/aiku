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
use App\Actions\UI\Mail\MailDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', MailDashboard::class)->name('dashboard');

Route::get('/mailrooms', IndexMailrooms::class)->name('mailrooms.index');
Route::get('/mailrooms/{mailroom}', ShowMailroom::class)->name('mailrooms.show');
Route::get('/mailrooms/{mailroom}/outboxes', [IndexOutboxes::class, 'inMailroom'])->name('mailrooms.show.outboxes.index');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}', [ShowOutbox::class, 'inMailroom'])->name('mailrooms.show.outboxes.show');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}/mailshots', [IndexMailshots::class, 'inMailroomInOutbox'])->name('mailrooms.show.outboxes.show.mailshots.index');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}/mailshots/{mailshot}', [ShowMailshot::class, 'inMailroomInOutbox'])->name('mailrooms.show.outboxes.show.mailshots.show');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}/mailshots/{mailshot}/dispatched-emails', [IndexDispatchedEmails::class, 'inMailroomInOutboxInMailshot'])->name('mailrooms.show.outboxes.show.mailshots.show.dispatched-emails.index');
Route::get('/mailrooms/{mailroom}/outboxes/{outbox}/mailshots/{mailshot}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, 'inMailroomInOutboxInMailshot'])->name('mailrooms.show.outboxes.show.mailshots.show.dispatched-emails.show');

Route::get('/mailrooms/{mailroom}/mailshots', [IndexMailshots::class, 'inMailroom'])->name('mailrooms.show.mailshots.index');
Route::get('/mailrooms/{mailroom}/mailshots/{mailshot}', [ShowMailroom::class, 'inMailroom'])->name('mailrooms.show.mailshots.show');
Route::get('/mailrooms/{mailroom}/mailshots/{mailshot}/dispatched-emails', [IndexDispatchedEmails::class, 'inMailroomInMailshot'])->name('mailrooms.show.mailshots.show.dispatched-emails.index');
Route::get('/mailrooms/{mailroom}/mailshots/{mailshot}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, 'inMailroomInMailshot'])->name('mailrooms.show.mailshots.show.dispatched-emails.show');

Route::get('/mailrooms/{mailroom}/dispatched-emails', [IndexDispatchedEmails::class, 'inMailroom'])->name('mailrooms.show.dispatched-emails.index');
Route::get('/mailrooms/{mailroom}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, 'inMailroom'])->name('mailrooms.show.dispatched-emails.show');

Route::get('/outboxes', IndexOutboxes::class)->name('outboxes.index');
Route::get('/outboxes/{outbox}', ShowOutbox::class)->name('outboxes.show');
Route::get('/outboxes/{outbox}/edit', EditOutbox::class)->name('outboxes.edit');

Route::get('/outboxes/{outbox}/mailshots', [IndexMailshots::class, 'inOutbox'])->name('outboxes.show.mailshots.index');
Route::get('/outboxes/{outbox}/mailshots/{mailshot}', [ShowMailshot::class, 'inOutbox'])->name('outboxes.show.mailshots.show');
Route::get('/outboxes/{outbox}/mailshots/{mailshot}/dispatched-emails', [IndexDispatchedEmails::class, 'inOutboxInMailshot'])->name('outboxes.show.mailshots.show.dispatched-emails.index');
Route::get('/outboxes/{outbox}/mailshots/{mailshot}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, 'inOutboxInMailshot'])->name('outboxes.show.mailshots.show.dispatched-emails.show');

Route::get('/outboxes/{outbox}/dispatched-emails', [IndexDispatchedEmails::class, 'inOutbox'])->name('outboxes.show.dispatched-emails.index');
Route::get('/outboxes/{outbox}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, 'inOutbox'])->name('outboxes.show.dispatched-emails.show');


Route::get('/mailshots', IndexMailshots::class)->name('mailshots.index');
Route::get('/mailshots/create', CreateMailshot::class)->name('mailshots.create');

Route::get('/mailshots/{mailshot}', ShowMailshot::class)->name('mailshots.show');
Route::get('/mailshots/{mailshot}/dispatched-emails', [IndexDispatchedEmails::class, 'inMailshot'])->name('mailshots.show.dispatched-emails.index');
Route::get('/mailshots/{mailshot}/dispatched-emails/{dispatchedEmail}', [ShowDispatchedEmail::class, 'inMailshot' ])->name('mailshots.show.dispatched-emails.show');

Route::get('/dispatched-emails', IndexDispatchedEmails::class)->name('dispatched-emails.index');
Route::get('/dispatched-emails/{dispatchedEmail}', ShowDispatchedEmail::class)->name('dispatched-emails.show');

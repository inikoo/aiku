<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 Nov 2023 15:59:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Mail\MailshotStats
 *
 * @property int $id
 * @property int $mailshot_id
 * @property int $number_estimated_dispatched_emails
 * @property string|null $estimated_dispatched_emails_calculated_at
 * @property int $number_emails
 * @property int $number_error_emails
 * @property int $number_rejected_emails
 * @property int $number_sent_emails
 * @property int $number_delivered_emails
 * @property int $number_hard_bounced_emails
 * @property int $number_soft_bounced_emails
 * @property int $number_opened_emails
 * @property int $number_clicked_emails
 * @property int $number_spam_emails
 * @property int $number_unsubscribed_emails
 * @property int $number_dispatched_emails
 * @property int $number_dispatched_emails_state_ready
 * @property int $number_dispatched_emails_state_error
 * @property int $number_dispatched_emails_state_rejected
 * @property int $number_dispatched_emails_state_sent
 * @property int $number_dispatched_emails_state_delivered
 * @property int $number_dispatched_emails_state_hard_bounce
 * @property int $number_dispatched_emails_state_soft_bounce
 * @property int $number_dispatched_emails_state_opened
 * @property int $number_dispatched_emails_state_clicked
 * @property int $number_dispatched_emails_state_spam
 * @property int $number_dispatched_emails_state_unsubscribed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats query()
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereEstimatedDispatchedEmailsCalculatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereMailshotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberClickedEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDeliveredEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDispatchedEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDispatchedEmailsStateClicked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDispatchedEmailsStateDelivered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDispatchedEmailsStateError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDispatchedEmailsStateHardBounce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDispatchedEmailsStateOpened($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDispatchedEmailsStateReady($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDispatchedEmailsStateRejected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDispatchedEmailsStateSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDispatchedEmailsStateSoftBounce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDispatchedEmailsStateSpam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberDispatchedEmailsStateUnsubscribed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberErrorEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberEstimatedDispatchedEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberHardBouncedEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberOpenedEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberRejectedEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberSentEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberSoftBouncedEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberSpamEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereNumberUnsubscribedEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MailshotStats extends Model
{
    protected $table = 'mailshot_stats';

    protected $guarded = [];


}

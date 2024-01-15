<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Nov 2023 16:48:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Mail\MailroomStats
 *
 * @property int $id
 * @property int|null $mailroom_id
 * @property int $number_outboxes
 * @property int $number_outbox_type_new_customer
 * @property int $number_outbox_type_invoice_deleted
 * @property int $number_outbox_type_new_order
 * @property int $number_outbox_type_shop_prospect
 * @property int $number_outbox_type_customer_prospect
 * @property int $number_outbox_type_marketing
 * @property int $number_outbox_type_newsletter
 * @property int $number_outbox_type_order_confirmation
 * @property int $number_outbox_type_password_reminder
 * @property int $number_outbox_type_registration
 * @property int $number_outbox_type_test
 * @property int $number_outbox_state_in_process
 * @property int $number_outbox_state_active
 * @property int $number_outbox_state_suspended
 * @property int $number_mailshots
 * @property int $number_mailshots_type_prospect_mailshot
 * @property int $number_mailshots_type_newsletter
 * @property int $number_mailshots_type_customer_prospect_mailshot
 * @property int $number_mailshots_type_marketing
 * @property int $number_mailshots_type_announcement
 * @property int $number_mailshots_state_in_process
 * @property int $number_mailshots_state_ready
 * @property int $number_mailshots_state_scheduled
 * @property int $number_mailshots_state_sending
 * @property int $number_mailshots_state_sent
 * @property int $number_mailshots_state_cancelled
 * @property int $number_mailshots_state_stopped
 * @property int $number_mailshots_type_prospect_mailshot_state_in_process
 * @property int $number_mailshots_type_prospect_mailshot_state_ready
 * @property int $number_mailshots_type_prospect_mailshot_state_scheduled
 * @property int $number_mailshots_type_prospect_mailshot_state_sending
 * @property int $number_mailshots_type_prospect_mailshot_state_sent
 * @property int $number_mailshots_type_prospect_mailshot_state_cancelled
 * @property int $number_mailshots_type_prospect_mailshot_state_stopped
 * @property int $number_mailshots_type_newsletter_state_in_process
 * @property int $number_mailshots_type_newsletter_state_ready
 * @property int $number_mailshots_type_newsletter_state_scheduled
 * @property int $number_mailshots_type_newsletter_state_sending
 * @property int $number_mailshots_type_newsletter_state_sent
 * @property int $number_mailshots_type_newsletter_state_cancelled
 * @property int $number_mailshots_type_newsletter_state_stopped
 * @property int $number_mailshots_type_customer_prospect_mailshot_state_in_proce
 * @property int $number_mailshots_type_customer_prospect_mailshot_state_ready
 * @property int $number_mailshots_type_customer_prospect_mailshot_state_schedule
 * @property int $number_mailshots_type_customer_prospect_mailshot_state_sending
 * @property int $number_mailshots_type_customer_prospect_mailshot_state_sent
 * @property int $number_mailshots_type_customer_prospect_mailshot_state_cancelle
 * @property int $number_mailshots_type_customer_prospect_mailshot_state_stopped
 * @property int $number_mailshots_type_marketing_state_in_process
 * @property int $number_mailshots_type_marketing_state_ready
 * @property int $number_mailshots_type_marketing_state_scheduled
 * @property int $number_mailshots_type_marketing_state_sending
 * @property int $number_mailshots_type_marketing_state_sent
 * @property int $number_mailshots_type_marketing_state_cancelled
 * @property int $number_mailshots_type_marketing_state_stopped
 * @property int $number_mailshots_type_announcement_state_in_process
 * @property int $number_mailshots_type_announcement_state_ready
 * @property int $number_mailshots_type_announcement_state_scheduled
 * @property int $number_mailshots_type_announcement_state_sending
 * @property int $number_mailshots_type_announcement_state_sent
 * @property int $number_mailshots_type_announcement_state_cancelled
 * @property int $number_mailshots_type_announcement_state_stopped
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Mail\Mailroom|null $mailroom
 * @method static Builder|MailroomStats newModelQuery()
 * @method static Builder|MailroomStats newQuery()
 * @method static Builder|MailroomStats query()
 * @method static Builder|MailroomStats whereCreatedAt($value)
 * @method static Builder|MailroomStats whereId($value)
 * @method static Builder|MailroomStats whereMailroomId($value)
 * @method static Builder|MailroomStats whereNumberClickedEmails($value)
 * @method static Builder|MailroomStats whereNumberDeliveredEmails($value)
 * @method static Builder|MailroomStats whereNumberDispatchedEmails($value)
 * @method static Builder|MailroomStats whereNumberDispatchedEmailsStateClicked($value)
 * @method static Builder|MailroomStats whereNumberDispatchedEmailsStateDelivered($value)
 * @method static Builder|MailroomStats whereNumberDispatchedEmailsStateError($value)
 * @method static Builder|MailroomStats whereNumberDispatchedEmailsStateHardBounce($value)
 * @method static Builder|MailroomStats whereNumberDispatchedEmailsStateOpened($value)
 * @method static Builder|MailroomStats whereNumberDispatchedEmailsStateReady($value)
 * @method static Builder|MailroomStats whereNumberDispatchedEmailsStateRejected($value)
 * @method static Builder|MailroomStats whereNumberDispatchedEmailsStateSent($value)
 * @method static Builder|MailroomStats whereNumberDispatchedEmailsStateSoftBounce($value)
 * @method static Builder|MailroomStats whereNumberDispatchedEmailsStateSpam($value)
 * @method static Builder|MailroomStats whereNumberDispatchedEmailsStateUnsubscribed($value)
 * @method static Builder|MailroomStats whereNumberEmails($value)
 * @method static Builder|MailroomStats whereNumberErrorEmails($value)
 * @method static Builder|MailroomStats whereNumberHardBouncedEmails($value)
 * @method static Builder|MailroomStats whereNumberMailshots($value)
 * @method static Builder|MailroomStats whereNumberMailshotsStateCancelled($value)
 * @method static Builder|MailroomStats whereNumberMailshotsStateInProcess($value)
 * @method static Builder|MailroomStats whereNumberMailshotsStateReady($value)
 * @method static Builder|MailroomStats whereNumberMailshotsStateScheduled($value)
 * @method static Builder|MailroomStats whereNumberMailshotsStateSending($value)
 * @method static Builder|MailroomStats whereNumberMailshotsStateSent($value)
 * @method static Builder|MailroomStats whereNumberMailshotsStateStopped($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeAnnouncement($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeAnnouncementStateCancelled($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeAnnouncementStateInProcess($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeAnnouncementStateReady($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeAnnouncementStateScheduled($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeAnnouncementStateSending($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeAnnouncementStateSent($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeAnnouncementStateStopped($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeCustomerProspectMailshot($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeCustomerProspectMailshotStateCancelle($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeCustomerProspectMailshotStateInProce($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeCustomerProspectMailshotStateReady($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeCustomerProspectMailshotStateSchedule($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeCustomerProspectMailshotStateSending($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeCustomerProspectMailshotStateSent($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeCustomerProspectMailshotStateStopped($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeMarketing($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeMarketingStateCancelled($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeMarketingStateInProcess($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeMarketingStateReady($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeMarketingStateScheduled($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeMarketingStateSending($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeMarketingStateSent($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeMarketingStateStopped($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeNewsletter($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeNewsletterStateCancelled($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeNewsletterStateInProcess($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeNewsletterStateReady($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeNewsletterStateScheduled($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeNewsletterStateSending($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeNewsletterStateSent($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeNewsletterStateStopped($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeProspectMailshot($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeProspectMailshotStateCancelled($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeProspectMailshotStateInProcess($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeProspectMailshotStateReady($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeProspectMailshotStateScheduled($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeProspectMailshotStateSending($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeProspectMailshotStateSent($value)
 * @method static Builder|MailroomStats whereNumberMailshotsTypeProspectMailshotStateStopped($value)
 * @method static Builder|MailroomStats whereNumberOpenedEmails($value)
 * @method static Builder|MailroomStats whereNumberOutboxStateActive($value)
 * @method static Builder|MailroomStats whereNumberOutboxStateInProcess($value)
 * @method static Builder|MailroomStats whereNumberOutboxStateSuspended($value)
 * @method static Builder|MailroomStats whereNumberOutboxTypeCustomerProspect($value)
 * @method static Builder|MailroomStats whereNumberOutboxTypeInvoiceDeleted($value)
 * @method static Builder|MailroomStats whereNumberOutboxTypeMarketing($value)
 * @method static Builder|MailroomStats whereNumberOutboxTypeNewCustomer($value)
 * @method static Builder|MailroomStats whereNumberOutboxTypeNewOrder($value)
 * @method static Builder|MailroomStats whereNumberOutboxTypeNewsletter($value)
 * @method static Builder|MailroomStats whereNumberOutboxTypeOrderConfirmation($value)
 * @method static Builder|MailroomStats whereNumberOutboxTypePasswordReminder($value)
 * @method static Builder|MailroomStats whereNumberOutboxTypeRegistration($value)
 * @method static Builder|MailroomStats whereNumberOutboxTypeShopProspect($value)
 * @method static Builder|MailroomStats whereNumberOutboxTypeTest($value)
 * @method static Builder|MailroomStats whereNumberOutboxes($value)
 * @method static Builder|MailroomStats whereNumberRejectedEmails($value)
 * @method static Builder|MailroomStats whereNumberSentEmails($value)
 * @method static Builder|MailroomStats whereNumberSoftBouncedEmails($value)
 * @method static Builder|MailroomStats whereNumberSpamEmails($value)
 * @method static Builder|MailroomStats whereNumberUnsubscribedEmails($value)
 * @method static Builder|MailroomStats whereUpdatedAt($value)
 * @mixin Eloquent
 */
class MailroomStats extends Model
{
    protected $table = 'mailroom_stats';

    protected $guarded = [];

    public function mailroom(): BelongsTo
    {
        return $this->belongsTo(Mailroom::class);
    }
}

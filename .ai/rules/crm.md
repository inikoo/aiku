---
paths:
  - 'app/Actions/CRM/Customer/**'
---

# CRM

## Anonymising a customer wipes the email reply address: reply first
`AnonymiseCustomer::anonymiseChats` sets `chat_sessions.metadata` to null on every chat session of the customer's web users, email-channel sessions included (`ProcessInboundEmail` links an email thread to the web user when the sender matches one). The reply address lives in that metadata, and `SendChatMessageByGmail` returns without sending, logging or throwing when both `email_reply_to` and `email_from` are missing. A reply sent after anonymising is stored in the chat but never emailed, with no error anywhere. Send any reply first, then anonymise. Guest email threads with no web user are not touched by the anonymiser.

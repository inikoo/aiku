---
paths:
  - 'app/Actions/Chat/**'
---

# Chat

## AI coverage: one outcome per customer message
chat_ai_outcomes holds one row per (session, trigger_message_id), enforced by partial unique indexes; write it only through StoreChatAiOutcome, which keeps the stronger outcome by ChatAiOutcomeEnum case order (draft first). GetChatAiCoverage counts each chat once, by the customer's latest message in the window: the recorded outcome, else a staff reply after it, else is_spam, else is_rubbish (as notice), else not worked. A newer customer message is new work, so an outcome on an earlier one no longer covers the chat. Team windows (today/overnight/team hours) come from config('chat.ai_teams').

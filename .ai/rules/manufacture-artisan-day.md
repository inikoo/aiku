---
paths:
  - 'app/Actions/Production/ManufactureArtisanDay/**'
---

# Manufacture Artisan Day

## Artisan days are recalculated from sessions whenever they are viewed
manufacture_artisan_days rows are rebuilt by HydrateManufactureArtisanDays for the period (max 92 days) whenever Daily hours, Performance or Payroll is opened. Never write their figures anywhere else. A supervisor's decision (resolution) only counts while resolved_minutes == recorded_minutes, so editing a session's times reopens the day. A day belongs to the shift it started in (night shifts go to the evening they began), and expected time = shift length minus the day's unpaid_break_minutes (work_schedule_days, default 30, HELP-4281). Timed WorkScheduleBreaks are not counted: they only mark when somebody is away (chat availability).

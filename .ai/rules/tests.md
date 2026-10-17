---
paths:
  - 'tests/**'
---

# Tests

## Tests share one database per parallel worker

Pest runs with `--parallel`. Each worker restores the dump ONCE and then runs many test
files in it, in an order paratest decides at runtime. Rows a test leaves behind are visible
to every later test in that worker, and which files share a worker changes run to run. A
test that passes alone and in its own file can still fail in CI.

When writing or fixing a test:

- Never assert an absolute count on a shared fixture. Read the baseline first, assert the delta.
- Never select a row by hard-coded id (`Pallet::find(4)`) or bare `first()`. Query for the
  invariants your assertions actually need, and exclude rows already used.
- Set the preconditions your assertions depend on instead of assuming the fixture arrives clean.
- Build fixtures through the `tests/Pest.php` helpers (`createGroup`, `createOrganisation`,
  `createShop`, ...), never through a bare model factory. The helpers return the FIRST row of
  their kind, so a factory-made half-seeded Group or Organisation left behind by any earlier
  file becomes the one every later test gets, and the failure surfaces far from its cause.
- Unit tests must not write to the database. A hand-rolled transaction is not enough of a
  guard: anything written on another connection, or in a job, survives the rollback.
- Reset any process-wide static you rely on (e.g. model auditing) inside the test itself.

A red CI run is usually several unrelated causes at once, and `--stop-on-defect` hides the
rest. Fix the one that fails every run first, then re-read the log.

## Big test files are split by topic; add to the topic file
ChatTest.php, ProcurementTest.php, ProductionTest.php and TicketsTest.php no longer exist. Chat tests live in tests/Feature/Chat{Session,Queries,Queue,Inbox,Permissions,Gmail,EmailReply,Whatsapp,Triage,Ai,StaffMessaging,StaffTasks}Test.php, production tests in tests/Feature/Production{Setup,Floor,Steps,Payroll,Stock,Board,Families}Test.php, tickets tests in tests/Feature/Tickets{Lifecycle,Access,Attachments,Qa,Board,Notifications,Integrations}Test.php and procurement tests in tests/Feature/Procurement*Test.php, with shared helpers in tests/Support/{Chat,Production,Procurement}Helpers.php (require_once). The chained ->depends('create production') tests all stay in ProductionSetupTest. Add a new test to the file for its topic and a shared helper to the Support file; never recreate the old monolith. Keep files under ~2,000 lines so paratest balances and a change only reruns a small chunk. Each file builds its own fixtures (createShop/createCustomer($this->shop)); never borrow ChatSession::find(1), Model::first() or inRandomOrder() rows another file may have left.

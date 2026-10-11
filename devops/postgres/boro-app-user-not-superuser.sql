-- INI-071 part 3: the app's database user stops being a PostgreSQL superuser.
-- Run on boro as postgres:  sudo -u postgres psql -d aiku -f boro-app-user-not-superuser.sql
-- Then, as aiku in the release folder:  php artisan mcp:sync-sql-roles
--
-- aiku keeps owning every table, so the app, migrations and day to day repairs in
-- psql work as before. What it loses is what only a superuser can do: COPY to or
-- from a file or program on the server, CREATE EXTENSION, ALTER SYSTEM,
-- session_replication_role, and touching objects it does not own.
--
-- Admin access stays, without a password, for the unix users listed in
-- /etc/postgresql/18/main/pg_ident.conf:
--     supers  postgres  postgres
--     supers  aiku      postgres
--     supers  inikoo    postgres
-- with the first line of pg_hba.conf changed to
--     local   all   postgres   peer map=supers
-- and a reload. From then on, logged in as aiku on boro:
--     psql              the app's user, owner of the tables, not superuser
--     psql -U postgres  superuser
-- Set this up and try it BEFORE running this file.
--
-- Undo:  ALTER ROLE aiku SUPERUSER;

\set ON_ERROR_STOP on

BEGIN;

SELECT format('GRANT %I TO aiku WITH ADMIN OPTION', rolname)
FROM pg_roles
WHERE rolname LIKE 'mcp\_aiku\_%'
\gexec

ALTER ROLE aiku SET createrole_self_grant = 'set, inherit';
GRANT pg_monitor TO aiku;
ALTER ROLE aiku NOSUPERUSER CREATEROLE;

GRANT pg_read_all_data TO staging;
ALTER ROLE staging NOSUPERUSER;
ALTER ROLE aiku_test NOSUPERUSER;

COMMIT;

SELECT rolname, rolsuper, rolcreaterole, rolcanlogin
FROM pg_roles
WHERE rolsuper OR rolname IN ('aiku', 'staging', 'aiku_test')
ORDER BY rolname;

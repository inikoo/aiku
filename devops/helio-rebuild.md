# Rebuilding helio

helio's two NVMe disks are worn out and striped (RAID 0), so it is planned to die (INI-086).
Nothing on it is the only copy of anything we need. While it is down production keeps running;
what stops is listed below, with where each piece comes back from.

| On helio | Comes back from | While helio is down |
| --- | --- | --- |
| Nightly production dump + R2 copy (`/etc/cron.d/aiku-db-backup`) | the cron file in `devops/cron/` | boro's `/home/inikoo/backup_aiku.sh` still dumps to the storage box at 03:30 MYT; boro's 12:00 `--check` alerts that helio's dump is missing |
| `archive` cluster, port 5434 (`aiku_archive`) | pgBackRest on the storage box: weekly full + WAL | archived data cannot be read |
| `delayed` cluster, port 5435 | rebuilt from boro, about 2 hours | no 12-hour undo window |
| WordPress: aw-advantage.com, aw-dropship.info, greenmansoaps.earth, canary.aw-advantage.com | `wp-backup` nightly: `/sel/wp-backup/db/*.sql.gz` and `/sel/wp-backup/files/` (= `/home/awa`) | sites down |
| wowsbar.com / awads.io closed page, delivery.wowsbar.com | nginx config in the config backup | down |
| Staging, aiku-sandbox.uk | `devops/refresh-staging.sh` from boro | down |
| GitHub Actions runner + `ci` cluster | `devops/setup-ci-runner.sh` | CI jobs wait in the queue |
| NightOwl (`main` cluster, `nightowl` db) | nothing: starts empty | no telemetry |
| Configuration, keys, tokens | `helio-config-YYYY-MM-DD.tar.gpg`, nightly, on `/sel/aiku/secrets` and R2 `aiku-db-backups` | — |

## 1. Get the configuration

On the Mac with the `aiku-backup` private key (the storage box is in the same Hetzner site as
helio; R2 is not):

    rclone copy r2:aiku-db-backups/helio-config-YYYY-MM-DD.tar.gpg .     # or from the storage box
    gpg --decrypt helio-config-YYYY-MM-DD.tar.gpg | tar -xf - -C helio-config

It holds `etc/`, `usr/local/`, `var/spool/cron/`, `root/.ssh`, `root/.config/rclone`,
`var/lib/postgresql/.ssh` and the staging `.env` files. Copy pieces back from it; never untar
`etc/` over a fresh install.

## 2. New server

- Ubuntu 24.04, **RAID 1**, 2 TB disks or larger (helio used 1.4 TB). Hostname `helio`.
- Private network: `10.0.0.2/24` on the second NIC, vSwitch `10.0.1.2/24` on `vlan4000`
  (copy `etc/netplan/`). boro and litio reach helio on 10.0.0.2.
- Packages: `postgresql-18 pgbackrest rclone nginx haproxy varnish php8.4-fpm mysql-server
  redis-server supervisor typesense-server cifs-utils`, and
  `apt install --no-install-recommends smartmontools` (the recommends pull in a mail server).
- Storage box on `/sel`: the `cifs` line from `etc/fstab` and `etc/backup-credentials.txt`.
- `etc/aiku-alerts.env`, `etc/aiku-backup.pub`, `etc/aiku-server-metrics.env`,
  `usr/local/bin/*`, `usr/local/sbin/*`, `root/.ssh`, `root/.config/rclone`,
  `var/lib/postgresql/.ssh` (owner postgres, mode 600 on the keys).

**If the public IP changes:** the R2 token is IP-filtered (65.109.156.41 and
2a01:4f9:3080:1315::/64), so edit the token in Cloudflare first or every R2 copy fails. Point
the Cloudflare DNS records of the sites above at the new IP.

## 3. Bring things back, most important first

1. **Production dump.** `etc/cron.d/aiku-db-backup` after the delayed replica (step 3) exists.
   Until then boro's own dump covers the night.
2. **Archive database.**

        pg_createcluster 18 archive -p 5434
        cp etc/pgbackrest.conf /etc/pgbackrest.conf
        rm -rf /var/lib/postgresql/18/archive/*
        sudo -u postgres pgbackrest --stanza=archive --delta restore
        cp -r etc/postgresql/18/archive/* /etc/postgresql/18/archive/
        pg_ctlcluster 18 archive start
        install -m 644 devops/cron/pgbackrest-archive /etc/cron.d/pgbackrest-archive

3. **Delayed replica:** the build steps at the top of `devops/postgres/helio-delayed.conf`.
   Drop the old slot on boro first if it is still there:
   `select pg_drop_replication_slot('replica_helio_delayed')`.
   Then `etc/cron.d/aiku-db-backup` and run `/usr/local/bin/aiku-db-backup --self-test`.
4. **WordPress sites:** MySQL databases `awa`, `awd_info`, `canary_woo`, `greenmansoaps_db` from
   `/sel/wp-backup/db/`; `rsync -a /sel/wp-backup/files/ /home/awa/`; nginx sites from
   `etc/nginx/sites-available/`; certificates from `etc/letsencrypt/` (or `certbot` again);
   `etc/cron.d/wp-backup`.
5. **Staging:** `etc/supervisor/conf.d/aiku-staging-*`, the staging `.env` files, then
   `/usr/local/sbin/refresh-staging` (the Sunday cron in `var/spool/cron/crontabs/root`).
6. **CI:** `devops/setup-ci-runner.sh`, then register the runner again in GitHub (the old
   registration dies with the disk).
7. **The rest:** `etc/cron.d/` (disk-alert, actions-runner-watchdog,
   nightowl-recycle-connections), `devops/systemd/aiku-server-metrics.service`, typesense,
   varnish, haproxy. NightOwl starts with an empty database.

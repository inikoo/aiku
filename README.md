#### Multi tenancy

We use PostgreSQL 12

sudo apt update; sudo apt upgrade;

Install timescale

https://docs.timescale.com/latest/getting-started/installation/ubuntu/installation-apt-ubuntu

CREATE DATABASE master ENCODING 'UTF8' LC_COLLATE = 'en_GB.UTF-8' LC_CTYPE = 'en_GB.UTF-8' TEMPLATE template0 ;

CREATE EXTENSION IF NOT EXISTS timescaledb CASCADE;

art migrate:refresh --path=database/migrations/landlord --database=landlord;art db:seed;art tenants:artisan "migrate --database=tenant --seed"

art tenants:artisan "migrate:refresh --database=tenant"; art tenants:artisan "migrate --database=tenant --seed";



##### Landlord database migrations and seeding (only for master)
art migrate --path=database/migrations/landlord --database=landlord
art db:seed



##### Tenants database migrations and seeding (for the tenants databases)
art tenants:artisan "migrate --database=tenant"
 
 art tenants:artisan "migrate:rollback --step=1 --database=tenant"

 
art tenants:artisan "migrate --database=tenant --seed"


## create model 
`art make:model Flight --migration`
## create seeder 
`php artisan make:seeder StoreSeeder`


## create factory 
`php artisan make:factory PostFactory`




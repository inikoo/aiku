
Create database master 


#### Multi tenancy

<<<<<<< Updated upstream


##### Landlord database migrations
`php artisan migrate --path=database/migrations/landlord --database=landlord`

##### Tenants database migrations
`php artisan tenants:artisan "migrate --database=tenant"`
##### Tenants database seeding
`php artisan tenants:artisan "migrate --database=tenant --seed`
##### Make Model 
`php artisan make:model Flight --migration`
=======
##### Landlord database migrations and seedngs (only for master)
`art migrate --path=database/migrations/landlord --database=landlord`
`art db:seed` 


##### Tenants database migrations and seddings (for the teneants databses)
`art tenants:artisan "migrate --database=tenant"` 
`art tenants:artisan "migrate --database=tenant --seed"` 


## create model 
`art make:model Flight --migration`
## create seeder 
`php artisan make:seeder StoreSeeder`






>>>>>>> Stashed changes

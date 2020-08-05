#### Multi tenancy

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


## create factory 
`php artisan make:factory PostFactory`




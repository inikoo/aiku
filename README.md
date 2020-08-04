
Create database master 


#### Multi tenancy



##### Landlord database migrations
`php artisan migrate --path=database/migrations/landlord --database=landlord`

##### Tenants database migrations
`php artisan tenants:artisan "migrate --database=tenant"`
##### Tenants database seeding
`php artisan tenants:artisan "migrate --database=tenant --seed`
##### Make Model 
`php artisan make:model Flight --migration`
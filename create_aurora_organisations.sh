#!/bin/bash

php artisan group:create awg 'AW Gifts' GB GBP
php artisan org:create awg shop aw katka@ancientwisdom.biz 'Ancient Wisdom Deals' GB GBP -s '{"type":"Aurora","db_name":"dw"}' --address '{"address_line_1":"Affinity Park","address_line_2":"Europa Drive","locality":"Sheffield","postal_code":"S9 1XT","country_id":48}'
php artisan workplace:create aw "Affinity Park" hq --settings '{"address_link":"Organisation:default"}'
php artisan org:create awg shop sk tomas@awgifts.eu 'AW gifts' SK EUR -s '{"type":"Aurora","db_name":"sk"}' --address '{"address_line_1":"CTPark Trnava","address_line_2":"Prílohy 583/57","locality":"Zavar","postal_code":"919 26","country_id":177}'
php artisan workplace:create sk "CTPark" hq --settings '{"address_link":"Organisation:default"}'
php artisan org:create awg shop es toni@awartisam.es 'AW Artisan' ES EUR -s '{"type":"Aurora","db_name":"es"}' --address '{"address_line_1":"Castelao Street 40 - 42","dependant_locality":"Guadalhorce","locality":"Málaga","postal_code":"29004","country_id":95}'
php artisan workplace:create es "Castelao 40" hq --settings '{"address_link":"Organisation:default"}'
php artisan org:create awg shop aroma erik@aw-aromatics.com 'Aromatics' GB GBP -s '{"type":"Aurora","db_name":"aroma"}' --address '{"address_line_1":"Unit 10-15, Parkwood Business Park","address_line_2":"Parkwood Road","locality":"Sheffield","postal_code":"S3 8AL","country_id":48}'
php artisan workplace:create aroma "Parkwood" hq --settings '{"address_link":"Organisation:default"}'

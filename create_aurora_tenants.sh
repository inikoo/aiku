php artisan create:group awg 'AW Gifts' GBP
php artisan create:tenant aw katka@ancientwisdom.biz 'Ancient Wisdom Marketing' GB GBP -g awg -s '{"type":"Aurora","db_name"
:"dw"}'
php artisan create:tenant sk tomas@awgifts.eu 'AW gifts' SK EUR -g awg -s '{"type":"Aurora","db_name":"sk"}'
php artisan create:tenant es toni@awartisam.es 'AW Artisan' ES EUR -g awg -s '{"type":"Aurora","db_name":"es"}'
php artisan create:tenant aroma erik@aw-aromatics.com 'Aromatics' GB GBP -g awg -s '{"type":"Aurora","db_name":"aroma"}'
## FIRST TIME RUNNING DOCKER ##

# docker compose build
# docker compose up -d

# docker compose exec app composer install
# docker compose exec app php artisan key:generate
# docker compose exec app php artisan migrate:fresh --seed



## OPTIONAL FOR FIRST AND WHEN CHANGE ROUTE OR CONFIG OR CODE ##

# docker compose exec app php artisan config:clear
# docker compose exec app php artisan cache:clear
# docker compose exec app php artisan route:clear
# docker compose exec app php artisan permission:cache-reset



## TO SEE LARAVEL.LOG WHEN CATCH ERROR DURING HIT API ##

# docker exec it -it laravel-app bash
# cd storage/logs
# cat laravel.log
# tail -f laravel.log (for see activity log live)



## USE IT IF WANT TO RUNNING PHP ARTISAN AND COMPOSER (RECOMMENDED IN LOCAL NOT IN DOCKER) ##

# docker compose exec app php artisan ...
# docker compose exec app composer require ...



## WHEN TO MAKE DOCKER DOWN ##

# docker compose down
# docker compose down -v (this action make database cache and service deleted but cache app still exist)

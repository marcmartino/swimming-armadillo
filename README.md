swimming-armadillo
==================

## First Timers
1. 'composer install'
2. 'php app/console doctrine:database:create'
3. Steps 2- of "Updating"

## Updating

1. 'composer update'
2. 'php app/console doctrine:migrations:migrate'
3. 'php app/console doctrine:schema:update --force'
4. 'php app/console server:run'
5. http://localhost:8000/withings/authorize
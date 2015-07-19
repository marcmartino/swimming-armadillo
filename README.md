swimming-armadillo
==================

## First Timers
1. 'composer install'
2. 'php app/console doctrine:database:create'
3. Steps 2- of "Updating"
4. Add hdlbit.com to your hosts file (127.0.0.1)
5. Disable apache if need be

## Updating

1. 'composer update'
2. 'php app/console doctrine:schema:update --force'
3. 'php app/console doctrine:migrations:migrate'
4. 'sudo php app/console server:run hdlbit.com:80 -v'
5. http://localhost:8000/withings/authorize

## Some API Stuff
http://localhost:8000/userdata/fatfreemass
http://localhost:8000/userdata/weight
http://localhost:8000/userdata/heartrate
http://localhost:8000/userdata/fatratio
http://localhost:8000/userdata/height
http://localhost:8000/userdata/....

## Installing Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/bin/composer

## Installing Postgres + PDO (TODO)
install homebrew
install php56 with pdo + pgsql
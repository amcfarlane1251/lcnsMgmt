##Installation
After you clone the git repo, change directory to project root and install the following dependencies

###Composer
curl -sS https://getcomposer.org/installer | php

###App Dependencies
php composer.phar install --no-dev --prefer-source

###Run Install
php artisan app:install --env=production

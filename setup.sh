#/bin/bash
BACKEND_DIR="$(pwd)"
FRONTEND_DIR="$(pwd)/frontend"

# pnpm install --dir $FRONTEND_DIR

## PHP

# init php configuration
sudo cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

sudo chmod a+x $BACKEND_DIR/artisan

if [ -f "$BACKEND_DIR/composer.json" ];then 
  composer install -d $BACKEND_DIR;
  PATH="$PATH:$BACKEND_DIR/vendor/bin"
fi

sudo rm -rf /var/www/html 
sudo ln -s "$BACKEND_DIR/public" /var/www/html; 
sudo sed -i 's/Listen 80$//' /etc/apache2/ports.conf
sudo sed -i 's/<VirtualHost \*:80>/ServerName 127.0.0.1\n<VirtualHost \*:8080>/' /etc/apache2/sites-enabled/000-default.conf

# enable mod_rewrite
sudo a2enmod rewrite

# install codespaces php extensions
# https://github.com/orgs/community/discussions/58212

# disable xdebug
sudo sed -i 's/xdebug.start_with_request = yes/xdebug.start_with_request = no/g' /usr/local/etc/php/conf.d/xdebug.ini

## pdo_mysql
wget https://www.php.net/distributions/php-$PHP_VERSION.tar.gz -O /tmp/php.tar.gz
tar -xzf /tmp/php.tar.gz -C /tmp
cd /tmp/php-$PHP_VERSION/ext/pdo_mysql
phpize
./configure
make
sudo make install
sudo sed -i 's/;extension=pdo_mysql/extension=pdo_mysql/' $PHP_INI_DIR/php.ini

# pcntl
cd /tmp/php-$PHP_VERSION/ext/pcntl
phpize
./configure
make
sudo make install
echo "extension=pcntl.so" | sudo tee  "$PHP_INI_DIR/conf.d/20-pcntl.ini"

composer install && yarn install --latest

# # MySQL
# docker pull $MYSQL_DOCKER_IMAGE
# if [ $(docker ps -a -f name=$MYSQL_CONTAINER_NAME | wc -l) -lt 1 ]; then
#   docker run --name $MYSQL_CONTAINER_NAME -e MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD -p 3306:3306 -d $MYSQL_DOCKER_IMAGE
#   sleep 10;
# fi
# MYSQL_RUNNING_STATUS=$(docker inspect --format='{{.State.Running}}' $MYSQL_CONTAINER_NAME)
# if [ $MYSQL_RUNNING_STATUS != "running" ]; then
#   echo -e "Starting MySQL container $MYSQL_CONTAINER_NAME"
#   docker start $MYSQL_CONTAINER_NAME
#   sleep 10;
# fi

# MYSQL_IP=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $MYSQL_CONTAINER_NAME)
# #IP_ADDRESS=$(docker inspect --format='{{.NetworkSettings.IPAddress}}' mysql-test)
# docker run -it --rm $MYSQL_DOCKER_IMAGE mysql -h$MYSQL_IP -uroot -p$MYSQL_ROOT_PASSWORD -e "CREATE DATABASE $MYSQL_DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# # Laravel
# php $BACKEND_DIR/artisan migrate:fresh
# php $BACKEND_DIR/artisan db:seed
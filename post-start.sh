#/bin/bash
# MySQL
MYSQL_DOCKER_IMAGE="mysql:5.7"
MYSQL_CONTAINER_NAME="laravel_mysql"
MYSQL_ROOT_PASSWORD=$(grep "DB_PASSWORD" .env | cut -d '=' -f2)
MYSQL_DB_NAME=$(grep "DB_DATABASE" .env | cut -d '=' -f2)

docker pull $MYSQL_DOCKER_IMAGE

# Check if the MySQL container exists; if not, create it.
if [ -z "$(docker ps -a -q -f name=$MYSQL_CONTAINER_NAME)" ]; then
  docker run --name $MYSQL_CONTAINER_NAME -e MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD -p 3306:3306 -d $MYSQL_DOCKER_IMAGE
  
  # Wait for MySQL to be ready for connections
  echo "Waiting for MySQL to be ready..."
  until docker exec $MYSQL_CONTAINER_NAME mysqladmin ping -h localhost -u root -p$MYSQL_ROOT_PASSWORD --silent; do
    echo "MySQL is starting up, waiting 1 second..."
    sleep 1
  done
  echo "MySQL is ready!"
fi

MYSQL_RUNNING_STATUS=$(docker inspect --format='{{.State.Running}}' $MYSQL_CONTAINER_NAME)
if [ "$MYSQL_RUNNING_STATUS" != "true" ]; then
  echo "Starting MySQL container $MYSQL_CONTAINER_NAME"
  docker start $MYSQL_CONTAINER_NAME
  # Wait for MySQL to be ready for connections
  until docker exec $MYSQL_CONTAINER_NAME mysqladmin ping -h localhost -u root -p$MYSQL_ROOT_PASSWORD --silent; do
    echo "MySQL is starting up, waiting 1 second..."
    sleep 1
  done
  echo "MySQL is ready!"
fi

# Check if database "$MYSQL_DB_NAME" exists; if not, create it.
DB_RESULT=$(docker exec $MYSQL_CONTAINER_NAME mysql -u root -p$MYSQL_ROOT_PASSWORD -e "SHOW DATABASES LIKE '$MYSQL_DB_NAME';" | grep "$MYSQL_DB_NAME")
if [ -z "$DB_RESULT" ]; then
  echo "Database $MYSQL_DB_NAME does not exist, creating it..."
  docker exec $MYSQL_CONTAINER_NAME mysql -u root -p$MYSQL_ROOT_PASSWORD -e "CREATE DATABASE $MYSQL_DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci;"
else
  echo "Database $MYSQL_DB_NAME already exists. Skipping creation."
fi

php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
apachectl start 
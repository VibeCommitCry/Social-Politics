#!/bin/sh
set -e

# The bind mount hides the image's vendor/, so install into the mounted folder when it is missing
if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction
fi

# Wait for DB to be reachable (simple retry loop)
until php bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; do
  echo "Waiting for database..."
  sleep 2
done

php bin/console doctrine:migrations:migrate -n --allow-no-migration

exec "$@"
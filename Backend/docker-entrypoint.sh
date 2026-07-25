#!/bin/sh
set -e

# Wait for DB to be reachable (simple retry loop)
until php bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; do
  echo "Waiting for database..."
  sleep 2
done

php bin/console doctrine:migrations:migrate -n --allow-no-migration

exec "$@"
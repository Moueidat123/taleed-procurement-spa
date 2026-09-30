#!/bin/bash
# First-initialisation only (runs when the local MySQL data volume is empty).
# Creates separate identities: runtime (data only), migrator (schema), tests.
# Passwords come from the container environment (infra/local/.env), never Git.
set -euo pipefail

: "${PROCUREMENT_DB:?}" "${PROCUREMENT_APP_PASSWORD:?}" "${PROCUREMENT_MIGRATOR_PASSWORD:?}" "${PROCUREMENT_TEST_PASSWORD:?}"

mysql --protocol=socket -uroot -p"${MYSQL_ROOT_PASSWORD}" <<SQL
CREATE DATABASE IF NOT EXISTS \`${PROCUREMENT_DB}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS \`${PROCUREMENT_DB}_test\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'procurement_app'@'%' IDENTIFIED BY '${PROCUREMENT_APP_PASSWORD}';
GRANT SELECT, INSERT, UPDATE, DELETE ON \`${PROCUREMENT_DB}\`.* TO 'procurement_app'@'%';

CREATE USER IF NOT EXISTS 'procurement_migrator'@'%' IDENTIFIED BY '${PROCUREMENT_MIGRATOR_PASSWORD}';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX, REFERENCES, CREATE VIEW, SHOW VIEW, TRIGGER, LOCK TABLES
  ON \`${PROCUREMENT_DB}\`.* TO 'procurement_migrator'@'%';

CREATE USER IF NOT EXISTS 'procurement_test'@'%' IDENTIFIED BY '${PROCUREMENT_TEST_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${PROCUREMENT_DB}_test\`.* TO 'procurement_test'@'%';

FLUSH PRIVILEGES;
SQL
echo "procurement: databases and least-privilege accounts initialised"

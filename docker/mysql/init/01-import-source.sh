#!/bin/bash
set -e

echo "[mysql-init] Importando biblioteca-2.sql en u_biblioteca_db..."
mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" u_biblioteca_db < /seed/biblioteca-2.sql
echo "[mysql-init] Importacion de la base fuente completada."

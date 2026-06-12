-- La base principal `biblioteca_unamad` ya la crea MYSQL_DATABASE.
-- Aqui creamos la segunda base (fuente de sincronizacion, conexion mysql2).
CREATE DATABASE IF NOT EXISTS `u_biblioteca_db`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

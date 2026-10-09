<?php
/**
 * HerMart - Local DB configuration override.
 *
 * Copy this file to `includes/config.local.php` and fill in your
 * own MySQL credentials. The constants here override the defaults
 * declared in `includes/db.php`. This file is meant to be local-only
 * and should not be committed with real secrets.
 *
 * Example for a typical local MySQL install using the unix socket
 * plugin (Debian/Ubuntu default for the `root` user):
 *
 *   DB_HOST = 'localhost'
 *   DB_USER = 'root'
 *   DB_PASS = ''              // empty only works via unix socket
 *   DB_NAME = 'hermart'
 *   DB_PORT = 3306
 *
 * If your MySQL is configured with `unix_socket` auth for root,
 * PHP connecting over TCP will be denied. Either:
 *   (a) create a dedicated user:
 *         CREATE USER 'hermart'@'localhost' IDENTIFIED BY 'yourpass';
 *         GRANT ALL PRIVILEGES ON hermart.* TO 'hermart'@'localhost';
 *         FLUSH PRIVILEGES;
 *       and use those credentials here, or
 *   (b) connect via the socket by setting DB_HOST = 'localhost:/var/run/mysqld/mysqld.sock'
 *       (path may differ on your system).
 */

// define('DB_HOST', 'localhost');
// define('DB_USER', 'hermart');
// define('DB_PASS', 'yourpassword');
// define('DB_NAME', 'hermart');
// define('DB_PORT', 3306);

<?php
/**
 * HerMart - Bootstrap.
 *
 * Include at the top of every page. Loads the DB layer, helpers, auth
 * primitives, and starts the session. Role-specific auth files
 * (customer-auth.php / seller-auth.php / admin-auth.php) are NOT loaded
 * automatically — pages call them as needed.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

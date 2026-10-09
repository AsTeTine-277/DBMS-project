<?php
/**
 * HerMart - Database Connection
 *
 * Uses MySQLi with prepared statements.
 * Returns a working connection; if MySQL is unavailable
 * the helpers return empty arrays / false so the page
 * still renders in a "demo / offline" state instead of
 * crashing. Pages that strictly require DB can check
 * db_ready().
 *
 * Credentials can be overridden by creating
 *   includes/config.local.php
 * with the constants DB_HOST, DB_USER, DB_PASS, DB_NAME,
 * DB_PORT defined before this file is included.
 */

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'hermart');
if (!defined('DB_PORT')) define('DB_PORT', 3306);

// Optional local override (each dev / host sets their own creds).
$localCfg = __DIR__ . '/config.local.php';
if (is_readable($localCfg)) {
    require_once $localCfg;
}

$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASS;
$db   = DB_NAME;
$port = DB_PORT;

mysqli_report(MYSQLI_REPORT_OFF);

$db_error = '';
$conn = @new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    // Try to create the database if it does not exist (helpful for first-time setup)
    $bootstrap = @new mysqli($host, $user, $pass, '', $port);
    if (!$bootstrap->connect_error) {
        $bootstrap->query("CREATE DATABASE IF NOT EXISTS `{$db}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $bootstrap->close();
        $conn = @new mysqli($host, $user, $pass, $db, $port);
    }
}

$db_ready = !$conn->connect_error;
if ($db_ready) {
    @$conn->set_charset('utf8mb4');
    @$conn->query("SET time_zone = '+06:00'");
} else {
    $db_error = $conn->connect_error ?? 'unknown error';
    error_log("[HerMart] DB connection failed: " . $db_error);
}

/**
 * Is the database ready?
 */
function db_ready() {
    global $db_ready;
    return $db_ready;
}

/**
 * Return the last DB connection error string, or ''.
 */
function db_error() {
    global $db_error;
    return (string)$db_error;
}

/**
 * Helper: run a prepared SELECT and return all rows as assoc array.
 */
function db_select($sql, $types = '', $params = []) {
    global $conn, $db_ready;
    if (!$db_ready) return [];
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('[HerMart] db_select prepare failed: ' . $conn->error . ' | SQL: ' . $sql);
        return [];
    }
    if ($types !== '' && count($params) > 0) {
        // bind_param requires references — build a referenced parameter array
        $refs = [];
        foreach ($params as $k => $v) {
            $refs[$k] = &$params[$k];
        }
        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }
    if (!$stmt->execute()) {
        error_log('[HerMart] db_select execute failed: ' . $stmt->error . ' | SQL: ' . $sql);
        $stmt->close();
        return [];
    }
    $res = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

/**
 * Helper: run a prepared SELECT and return single row.
 */
function db_select_one($sql, $types = '', $params = []) {
    $rows = db_select($sql, $types, $params);
    return $rows[0] ?? null;
}

/**
 * Helper: run INSERT / UPDATE / DELETE prepared statement.
 * Returns true on success, false on failure.
 */
function db_execute($sql, $types = '', $params = []) {
    global $conn, $db_ready;
    if (!$db_ready) return false;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('[HerMart] db_execute prepare failed: ' . $conn->error . ' | SQL: ' . $sql);
        return false;
    }
    if ($types !== '' && count($params) > 0) {
        $refs = [];
        foreach ($params as $k => $v) {
            $refs[$k] = &$params[$k];
        }
        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }
    $ok = $stmt->execute();
    if (!$ok) {
        error_log('[HerMart] db_execute execute failed: ' . $stmt->error . ' | SQL: ' . $sql);
    }
    $stmt->close();
    return $ok;
}

/**
 * Helper: get last insert id.
 */
function db_insert_id() {
    global $conn;
    return $conn ? $conn->insert_id : 0;
}

/**
 * Start a transaction when the database connection is available.
 */
function db_transaction_start() {
    global $conn, $db_ready;
    return $db_ready && $conn->begin_transaction();
}

/**
 * Commit the current transaction.
 */
function db_transaction_commit() {
    global $conn;
    return $conn && $conn->commit();
}

/**
 * Roll back the current transaction.
 */
function db_transaction_rollback() {
    global $conn;
    return $conn && $conn->rollback();
}

/**
 * Helper: escape string for safe HTML output.
 */
function h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

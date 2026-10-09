<?php


require_once __DIR__ . '/functions.php';


function redirect($path) {
    $path = (string)$path;

   
    if (preg_match('#^[a-z][a-z0-9+.\-]*://#i', $path) || strpos($path, '//') === 0) {
        header('Location: ' . $path);
        exit;
    }

   
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
    $appBase = '';
    $parts = explode('/', trim($scriptName, '/'));
    if (!empty($parts) && strpos($parts[0], '.') === false) {
        $appBase = '/' . $parts[0];
    }

    if ($path === '') {
        header('Location: /');
        exit;
    }

    $query = '';
    $route = $path;
    if (strpos($path, '?') !== false) {
        [$route, $query] = explode('?', $path, 2);
    }

    $clean = '/' . ltrim($route, '/\\');
    $target = $appBase === '' ? $clean : $appBase . $clean;
    if ($query !== '') {
        $target .= '?' . $query;
    }

    header('Location: ' . $target);
    exit;
}


function current_user() {
    return $_SESSION['user'] ?? null;
}


function is_logged_in() {
    return !empty($_SESSION['user']['id']);
}


function require_login() {
    if (!is_logged_in()) {
        flash_set('error', 'Please log in to continue.');
        redirect('login.php');
    }
}


function require_role($role) {
    require_login();
    if (($_SESSION['user']['role'] ?? '') !== $role) {
        flash_set('error', 'You do not have permission to view that page.');
        redirect('customer/home.php');
    }
}


function flash_set($type, $message) {
    $_SESSION['hm_flash'] = ['type' => $type, 'message' => $message];
}


function flash_get() {
    $f = $_SESSION['hm_flash'] ?? null;
    unset($_SESSION['hm_flash']);
    return $f;
}

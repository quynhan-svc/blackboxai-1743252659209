<?php
require_once __DIR__.'/includes/init.php';

// Basic routing
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($request) {
    case '/':
    case '':
        require __DIR__.'/admin/login.php';
        break;
    case '/admin':
        require __DIR__.'/admin/dashboard.php';
        break;
    default:
        if (file_exists(__DIR__.$request.'.php')) {
            require __DIR__.$request.'.php';
        } else {
            http_response_code(404);
            echo '404 Not Found';
        }
}
?>
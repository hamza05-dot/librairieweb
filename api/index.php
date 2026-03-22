<?php
/**
 * LibraireWeb REST API — Entry Point
 *
 * Works WITHOUT .htaccess — uses ?path= query string routing
 *
 * Usage:
 *   GET  /api/index.php?path=books
 *   GET  /api/index.php?path=books/5
 *   POST /api/index.php?path=auth/login
 *   GET  /api/index.php?path=categories
 *   etc.
 */

require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── Parse route from ?path= parameter ──
// Supports both:
//   index.php?path=books/5
//   clean URLs via .htaccess (fallback)
$pathParam = $_GET['path'] ?? '';

if ($pathParam) {
    // Remove leading slash if any
    $uri = trim($pathParam, '/');
} else {
    // Fallback: try to parse from REQUEST_URI (for .htaccess clean URLs)
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = preg_replace('#^/LibraireWeb/api/?#', '', $uri);
    $uri = preg_replace('#^index\.php/?#', '', $uri);
    $uri = trim($uri, '/');
}

// Remove ?path from GET so it doesn't interfere with other params
unset($_GET['path']);

$segments = array_values(array_filter(explode('/', $uri)));

$resource = $segments[0] ?? '';
$id       = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;
$sub      = $segments[2] ?? null; // e.g. "status" in orders/5/status

// ── Route ──
switch ($resource) {

    case 'books':
        require_once __DIR__ . '/books/books.php';
        handleBooks($method, $id);
        break;

    case 'categories':
        require_once __DIR__ . '/categories/categories.php';
        handleCategories($method, $id);
        break;

    case 'auth':
        $action = $segments[1] ?? '';
        require_once __DIR__ . '/auth/auth.php';
        handleAuth($method, $action);
        break;

    case 'users':
        require_once __DIR__ . '/users/users.php';
        handleUsers($method, $id);
        break;

    case 'orders':
        require_once __DIR__ . '/orders/orders.php';
        handleOrders($method, $id, $sub);
        break;

    default:
        sendJson([
            'name'    => 'LibraireWeb API',
            'version' => '1.0',
            'usage'   => 'Add ?path=resource to the URL',
            'examples' => [
                'GET  index.php?path=books',
                'GET  index.php?path=books/5',
                'GET  index.php?path=categories',
                'POST index.php?path=auth/login',
                'POST index.php?path=auth/register',
                'GET  index.php?path=orders',
                'POST index.php?path=orders',
            ],
            'routes'  => [
                'GET  ?path=books',
                'GET  ?path=books/{id}',
                'POST ?path=books',
                'PUT  ?path=books/{id}',
                'DELETE ?path=books/{id}',
                'GET  ?path=categories',
                'GET  ?path=categories/{id}',
                'POST ?path=categories',
                'PUT  ?path=categories/{id}',
                'DELETE ?path=categories/{id}',
                'POST ?path=auth/register',
                'POST ?path=auth/login',
                'GET  ?path=users',
                'GET  ?path=users/{id}',
                'PUT  ?path=users/{id}',
                'DELETE ?path=users/{id}',
                'GET  ?path=orders',
                'GET  ?path=orders/{id}',
                'POST ?path=orders',
                'PUT  ?path=orders/{id}/status',
            ]
        ]);
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?? false);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/api', '', $path);
$segments = explode('/', trim($path, '/'));

try {
    // Auth routes
    if ($path === '/auth/register' && $method === 'POST') {
        $controller = new App\Controllers\AuthController();
        $controller->register();
    } elseif ($path === '/auth/login' && $method === 'POST') {
        $controller = new App\Controllers\AuthController();
        $controller->login();
    } elseif ($path === '/auth/me' && $method === 'GET') {
        $controller = new App\Controllers\AuthController();
        $controller->getCurrentUser();
    }
    // Event routes
    elseif ($path === '/events' && $method === 'GET') {
        $controller = new App\Controllers\EventController();
        $controller->getEvents();
    } elseif ($path === '/events' && $method === 'POST') {
        $controller = new App\Controllers\EventController();
        $controller->createEvent();
    } elseif (preg_match('#^/events/(\d+)$#', $path, $matches) && $method === 'GET') {
        $controller = new App\Controllers\EventController();
        $controller->getEvent((int)$matches[1]);
    } elseif (preg_match('#^/events/(\d+)$#', $path, $matches) && $method === 'PUT') {
        $controller = new App\Controllers\EventController();
        $controller->updateEvent((int)$matches[1]);
    } elseif (preg_match('#^/events/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        $controller = new App\Controllers\EventController();
        $controller->deleteEvent((int)$matches[1]);
    } elseif (preg_match('#^/events/(\d+)/register$#', $path, $matches) && $method === 'POST') {
        $controller = new App\Controllers\EventController();
        $controller->registerForEvent((int)$matches[1]);
    } elseif (preg_match('#^/events/(\d+)/cancel$#', $path, $matches) && $method === 'POST') {
        $controller = new App\Controllers\EventController();
        $controller->cancelRegistration((int)$matches[1]);
    } elseif (preg_match('#^/events/(\d+)/attendees$#', $path, $matches) && $method === 'GET') {
        $controller = new App\Controllers\EventController();
        $controller->getRegisteredUsers((int)$matches[1]);
    } elseif (preg_match('#^/events/(\d+)/attendees/(\d+)$#', $path, $matches) && $method === 'POST') {
        $controller = new App\Controllers\EventController();
        $controller->markAttendance((int)$matches[1], (int)$matches[2]);
    } elseif ($path === '/events/search' && $method === 'GET') {
        $controller = new App\Controllers\EventController();
        $controller->searchEvents();
    }
    // Category routes
    elseif ($path === '/categories' && $method === 'GET') {
        $controller = new App\Controllers\CategoryController();
        $controller->getCategories();
    } elseif ($path === '/categories' && $method === 'POST') {
        $controller = new App\Controllers\CategoryController();
        $controller->createCategory();
    } elseif (preg_match('#^/categories/(\d+)$#', $path, $matches) && $method === 'GET') {
        $controller = new App\Controllers\CategoryController();
        $controller->getCategory((int)$matches[1]);
    } elseif (preg_match('#^/categories/(\d+)$#', $path, $matches) && $method === 'PUT') {
        $controller = new App\Controllers\CategoryController();
        $controller->updateCategory((int)$matches[1]);
    } elseif (preg_match('#^/categories/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        $controller = new App\Controllers\CategoryController();
        $controller->deleteCategory((int)$matches[1]);
    } elseif (preg_match('#^/categories/(\d+)/events$#', $path, $matches) && $method === 'GET') {
        $controller = new App\Controllers\EventController();
        $controller->findEventsByCategory((int)$matches[1]);
    }
    // User-specific routes
    elseif ($path === '/user/events' && $method === 'GET') {
        $controller = new App\Controllers\UserController();
        $controller->getUserEvents();
    } elseif ($path === '/user/created-events' && $method === 'GET') {
        $controller = new App\Controllers\UserController();
        $controller->getCreatedEvents();
    }
    // Default - Not Found
    else {
        App\Utils\Response::error('Endpoint not found', 404);
    }
} catch (Exception $e) {
    App\Utils\Response::error('Server error: ' . $e->getMessage(), 500);
}
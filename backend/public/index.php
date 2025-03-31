<?php

declare(strict_types=1);

use App\Services\Router;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?: false);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$router = new Router();
$router->add('/auth/register', ['controller' => 'Auth', 'action' => 'register', 'method' => 'POST']);
$router->add('/auth/login', ['controller' => 'Auth', 'action' => 'login', 'method' => 'POST']);
$router->add('/auth/me', ['controller' => 'Auth', 'action' => 'getCurrentUser', 'method' => 'GET']);
$router->add('/events', ['controller' => 'Event', 'action' => 'getEvents', 'method' => 'GET']);
$router->add('/events', ['controller' => 'Event', 'action' => 'createEvent', 'method' => 'POST']);
$router->add('/events/{id}', ['controller' => 'Event', 'action' => 'getEvent', 'method' => 'GET']);
$router->add('/events/{id}', ['controller' => 'Event', 'action' => 'updateEvent', 'method' => 'PUT']);
$router->add('/events/{id}', ['controller' => 'Event', 'action' => 'deleteEvent', 'method' => 'DELETE']);
$router->add('/events/{id}/register', ['controller' => 'Event', 'action' => 'registerForEvent', 'method' => 'POST']);
$router->add('/events/{id}/unregister', ['controller' => 'Event', 'action' => 'unregisterForEvent', 'method' => 'DELETE']);
$router->add('/events/{id}/attendees', ['controller' => 'Event', 'action' => 'getRegisteredUsers', 'method' => 'GET']);
$router->add('/events/{id}/attendees/{userId}', ['controller' => 'Event', 'action' => 'markAttendance', 'method' => 'POST']);
$router->add('/events/search', ['controller' => 'Event', 'action' => 'searchEvents', 'method' => 'GET']);
$router->add('/categories', ['controller' => 'Category', 'action' => 'getCategories', 'method' => 'GET']);
$router->add('/categories', ['controller' => 'Category', 'action' => 'createCategory', 'method' => 'POST']);
$router->add('/categories/{id}', ['controller' => 'Category', 'action' => 'getCategory', 'method' => 'GET']);
$router->add('/categories/{id}', ['controller' => 'Category', 'action' => 'updateCategory', 'method' => 'PUT']);
$router->add('/categories/{id}', ['controller' => 'Category', 'action' => 'deleteCategory', 'method' => 'DELETE']);
$router->add('/categories/{id}/events', ['controller' => 'Category', 'action' => 'getEventsByCategory', 'method' => 'GET']);
$router->add('/user/events', ['controller' => 'User', 'action' => 'getUserEvents', 'method' => 'GET']);
$router->add('/user/created-events', ['controller' => 'User', 'action' => 'getCreatedEvents', 'method' => 'GET']);
$router->add('/user/registered-events', ['controller' => 'User', 'action' => 'getRegisteredEvents', 'method' => 'GET']);
//echo "<pre>";
//var_dump($_SERVER);
//echo "</pre>";
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
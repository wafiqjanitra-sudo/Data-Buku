<?php

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/models/Buku.php';
require __DIR__ . '/../app/controllers/BukuController.php';

try {
    $db = (new Database())->connect();
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi database gagal. Periksa konfigurasi database.');
}

$model = new Buku($db);
$controller = new BukuController($model);

$page = $_GET['page'] ?? 'buku';
$action = $_GET['action'] ?? 'index';

if ($page !== 'buku') {
    http_response_code(404);
    exit('Halaman tidak ditemukan.');
}

$routes = [
    'index' => fn() => $controller->index(),
    'create' => fn() => $controller->create(),
    'store' => fn() => $controller->store(),
    'edit' => fn() => $controller->edit(),
    'update' => fn() => $controller->update(),
    'delete' => fn() => $controller->delete()
];

if (!isset($routes[$action])) {
    http_response_code(404);
    exit('Action tidak ditemukan.');
}

$routes[$action]();

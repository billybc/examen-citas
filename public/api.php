<?php

declare(strict_types=1);

require __DIR__ . '/autoload.php';

use Citas\Application\CitaService;
use Citas\Persistence\Database;
use Citas\Persistence\PdoCitaRepository;
use Citas\Presentation\CitaController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/api#', '', $uri);
$path = $path === '' ? '/' : $path;

$body = [];
$rawInput = file_get_contents('php://input');
if ($rawInput !== false && $rawInput !== '') {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $body = $decoded;
    }
}

$repository = new PdoCitaRepository(Database::connection());
$service = new CitaService($repository);
$controller = new CitaController($service);

$controller->handle($_SERVER['REQUEST_METHOD'], $path, $_GET, $body);

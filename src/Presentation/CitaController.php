<?php

declare(strict_types=1);

namespace Citas\Presentation;

use Citas\Application\CitaService;
use Citas\Domain\CitaConflictException;
use Citas\Domain\CitaNotFoundException;
use InvalidArgumentException;

final class CitaController
{
    public function __construct(private CitaService $service)
    {
    }

    public function handle(string $method, string $path, array $query, array $body): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // GET /api/doctores
            if ($method === 'GET' && $path === '/doctores') {
                $this->json(200, ['data' => $this->service->listDoctores()]);
                return;
            }

            // GET /api/pacientes
            if ($method === 'GET' && $path === '/pacientes') {
                $this->json(200, ['data' => $this->service->listPacientes()]);
                return;
            }

            // GET /api/citas
            if ($method === 'GET' && $path === '/citas') {
                $doctorId = isset($query['doctor_id']) ? (int) $query['doctor_id'] : null;
                $data = $this->service->listCitas($doctorId, $query['desde'] ?? null, $query['hasta'] ?? null);
                $this->json(200, ['data' => $data]);
                return;
            }

            // POST /api/citas
            if ($method === 'POST' && $path === '/citas') {
                $data = $this->service->createCita($body);
                $this->json(201, ['data' => $data]);
                return;
            }

            // GET /api/citas/{id}
            if ($method === 'GET' && preg_match('#^/citas/(\d+)$#', $path, $m)) {
                $data = $this->service->getCita((int) $m[1]);
                $this->json(200, ['data' => $data]);
                return;
            }

            // PUT /api/citas/{id}  (reprogramar - usado por drag & drop)
            if ($method === 'PUT' && preg_match('#^/citas/(\d+)$#', $path, $m)) {
                $data = $this->service->rescheduleCita((int) $m[1], $body);
                $this->json(200, ['data' => $data]);
                return;
            }

            // PATCH /api/citas/{id}/estado
            if ($method === 'PATCH' && preg_match('#^/citas/(\d+)/estado$#', $path, $m)) {
                $data = $this->service->changeStatus((int) $m[1], $body['estado'] ?? '');
                $this->json(200, ['data' => $data]);
                return;
            }

            $this->json(404, ['error' => 'not_found', 'message' => 'Ruta no encontrada.']);
        } catch (CitaConflictException $e) {
            $this->json(409, ['error' => 'availability_overlap', 'message' => $e->getMessage()]);
        } catch (CitaNotFoundException $e) {
            $this->json(404, ['error' => 'not_found', 'message' => $e->getMessage()]);
        } catch (InvalidArgumentException $e) {
            $this->json(400, ['error' => 'invalid_input', 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->json(500, ['error' => 'server_error', 'message' => 'Error inesperado.']);
        }
    }

    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}

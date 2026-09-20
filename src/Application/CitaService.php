<?php

declare(strict_types=1);

namespace Citas\Application;

use Citas\Domain\CitaConflictException;
use Citas\Domain\CitaNotFoundException;
use Citas\Domain\CitaRepositoryInterface;
use InvalidArgumentException;

final class CitaService
{
    private const ESTADOS_VALIDOS = ['pendiente', 'confirmada', 'cancelada', 'atendida'];

    public function __construct(private CitaRepositoryInterface $repository)
    {
    }

    public function listCitas(?int $doctorId, ?string $desde, ?string $hasta): array
    {
        return array_map(fn ($c) => $c->toArray(), $this->repository->all($doctorId, $desde, $hasta));
    }

    public function getCita(int $id): array
    {
        $cita = $this->repository->find($id);
        if ($cita === null) {
            throw new CitaNotFoundException("La cita {$id} no existe.");
        }

        return $cita->toArray();
    }

    public function createCita(array $data): array
    {
        $this->validarCamposObligatorios($data);
        $doctorId = (int) $data['doctor_id'];
        $pacienteId = (int) $data['paciente_id'];

        if (!$this->repository->doctorExists($doctorId)) {
            throw new InvalidArgumentException('El doctor indicado no existe.');
        }
        if (!$this->repository->pacienteExists($pacienteId)) {
            throw new InvalidArgumentException('El paciente indicado no existe.');
        }
        $this->validarRangoHorario($data['hora_inicio'], $data['hora_fin']);

        if ($this->repository->hasOverlap($doctorId, $data['fecha'], $data['hora_inicio'], $data['hora_fin'], null)) {
            throw new CitaConflictException('El horario se cruza con otra cita del mismo doctor.');
        }

        $id = $this->repository->create(
            $doctorId,
            $pacienteId,
            $data['fecha'],
            $data['hora_inicio'],
            $data['hora_fin'],
            $data['motivo'] ?? null
        );

        return $this->getCita($id);
    }

    public function rescheduleCita(int $id, array $data): array
    {
        $cita = $this->repository->find($id);
        if ($cita === null) {
            throw new CitaNotFoundException("La cita {$id} no existe.");
        }

        $fecha = $data['fecha'] ?? $cita->fecha;
        $horaInicio = $data['hora_inicio'] ?? $cita->horaInicio;
        $horaFin = $data['hora_fin'] ?? $cita->horaFin;
        $this->validarRangoHorario($horaInicio, $horaFin);

        if ($this->repository->hasOverlap($cita->doctorId, $fecha, $horaInicio, $horaFin, $id)) {
            throw new CitaConflictException('El nuevo horario se cruza con otra cita del mismo doctor.');
        }

        $this->repository->reschedule($id, $fecha, $horaInicio, $horaFin);

        return $this->getCita($id);
    }

    public function changeStatus(int $id, string $estado): array
    {
        if (!in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException('Estado no válido. Use: ' . implode(', ', self::ESTADOS_VALIDOS));
        }
        if ($this->repository->find($id) === null) {
            throw new CitaNotFoundException("La cita {$id} no existe.");
        }

        $this->repository->changeStatus($id, $estado);

        return $this->getCita($id);
    }

    public function listDoctores(): array
    {
        return $this->repository->listDoctores();
    }

    public function listPacientes(): array
    {
        return $this->repository->listPacientes();
    }

    private function validarCamposObligatorios(array $data): void
    {
        foreach (['doctor_id', 'paciente_id', 'fecha', 'hora_inicio', 'hora_fin'] as $campo) {
            if (empty($data[$campo])) {
                throw new InvalidArgumentException("El campo {$campo} es obligatorio.");
            }
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha'])) {
            throw new InvalidArgumentException('El formato de fecha debe ser YYYY-MM-DD.');
        }
    }

    private function validarRangoHorario(string $horaInicio, string $horaFin): void
    {
        if (!preg_match('/^\d{2}:\d{2}$/', $horaInicio) || !preg_match('/^\d{2}:\d{2}$/', $horaFin)) {
            throw new InvalidArgumentException('El formato de hora debe ser HH:MM.');
        }
        if ($horaInicio >= $horaFin) {
            throw new InvalidArgumentException('La hora de inicio debe ser menor que la hora de fin.');
        }
    }
}

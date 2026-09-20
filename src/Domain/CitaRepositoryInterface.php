<?php

declare(strict_types=1);

namespace Citas\Domain;

interface CitaRepositoryInterface
{
    /** @return array<int, Cita> */
    public function all(?int $doctorId, ?int $pacienteId, ?string $desde, ?string $hasta): array;

    public function find(int $id): ?Cita;

    public function create(int $doctorId, int $pacienteId, string $fecha, string $horaInicio, string $horaFin, ?string $motivo): int;

    public function reschedule(int $id, string $fecha, string $horaInicio, string $horaFin): void;

    public function changeStatus(int $id, string $estado): void;

    public function hasOverlap(int $doctorId, string $fecha, string $horaInicio, string $horaFin, ?int $excludeId): bool;

    public function doctorExists(int $doctorId): bool;

    public function pacienteExists(int $pacienteId): bool;

    /** @return array<int, array> */
    public function listDoctores(): array;

    /** @return array<int, array> */
    public function listPacientes(): array;
}

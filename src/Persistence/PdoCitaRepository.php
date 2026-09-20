<?php

declare(strict_types=1);

namespace Citas\Persistence;

use Citas\Domain\Cita;
use Citas\Domain\CitaRepositoryInterface;
use PDO;

final class PdoCitaRepository implements CitaRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(?int $doctorId, ?string $desde, ?string $hasta): array
    {
        $sql = 'SELECT * FROM citas WHERE 1=1';
        $params = [];

        if ($doctorId !== null) {
            $sql .= ' AND doctor_id = :doctor_id';
            $params['doctor_id'] = $doctorId;
        }
        if ($desde !== null) {
            $sql .= ' AND fecha >= :desde';
            $params['desde'] = $desde;
        }
        if ($hasta !== null) {
            $sql .= ' AND fecha <= :hasta';
            $params['hasta'] = $hasta;
        }
        $sql .= ' ORDER BY fecha, hora_inicio';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return array_map(fn (array $row) => $this->hydrate($row), $stmt->fetchAll());
    }

    public function find(int $id): ?Cita
    {
        $stmt = $this->pdo->prepare('SELECT * FROM citas WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    public function create(int $doctorId, int $pacienteId, string $fecha, string $horaInicio, string $horaFin, ?string $motivo): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO citas (doctor_id, paciente_id, fecha, hora_inicio, hora_fin, motivo, estado)
             VALUES (:doctor_id, :paciente_id, :fecha, :hora_inicio, :hora_fin, :motivo, "pendiente")'
        );
        $stmt->execute([
            'doctor_id' => $doctorId,
            'paciente_id' => $pacienteId,
            'fecha' => $fecha,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'motivo' => $motivo,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function reschedule(int $id, string $fecha, string $horaInicio, string $horaFin): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE citas SET fecha = :fecha, hora_inicio = :hora_inicio, hora_fin = :hora_fin WHERE id = :id'
        );
        $stmt->execute([
            'fecha' => $fecha,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'id' => $id,
        ]);
    }

    public function changeStatus(int $id, string $estado): void
    {
        $stmt = $this->pdo->prepare('UPDATE citas SET estado = :estado WHERE id = :id');
        $stmt->execute(['estado' => $estado, 'id' => $id]);
    }

    public function hasOverlap(int $doctorId, string $fecha, string $horaInicio, string $horaFin, ?int $excludeId): bool
    {
        $sql = 'SELECT COUNT(*) AS total FROM citas
                WHERE doctor_id = :doctor_id
                  AND fecha = :fecha
                  AND estado <> "cancelada"
                  AND hora_inicio < :hora_fin
                  AND hora_fin > :hora_inicio';
        $params = [
            'doctor_id' => $doctorId,
            'fecha' => $fecha,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
        ];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return ((int) $row['total']) > 0;
    }

    public function doctorExists(int $doctorId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS total FROM doctores WHERE id = :id');
        $stmt->execute(['id' => $doctorId]);

        return ((int) $stmt->fetch()['total']) > 0;
    }

    public function pacienteExists(int $pacienteId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS total FROM pacientes WHERE id = :id');
        $stmt->execute(['id' => $pacienteId]);

        return ((int) $stmt->fetch()['total']) > 0;
    }

    public function listDoctores(): array
    {
        return $this->pdo->query('SELECT id, nombre, especialidad FROM doctores ORDER BY nombre')->fetchAll();
    }

    public function listPacientes(): array
    {
        return $this->pdo->query('SELECT id, nombre, telefono FROM pacientes ORDER BY nombre')->fetchAll();
    }

    private function hydrate(array $row): Cita
    {
        return new Cita(
            id: (int) $row['id'],
            doctorId: (int) $row['doctor_id'],
            pacienteId: (int) $row['paciente_id'],
            fecha: $row['fecha'],
            horaInicio: substr($row['hora_inicio'], 0, 5),
            horaFin: substr($row['hora_fin'], 0, 5),
            motivo: $row['motivo'],
            estado: $row['estado'],
        );
    }
}

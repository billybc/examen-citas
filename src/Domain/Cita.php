<?php

declare(strict_types=1);

namespace Citas\Domain;

final class Cita
{
    public function __construct(
        public readonly int $id,
        public readonly int $doctorId,
        public readonly int $pacienteId,
        public readonly string $fecha,
        public readonly string $horaInicio,
        public readonly string $horaFin,
        public readonly ?string $motivo,
        public readonly string $estado,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'doctor_id' => $this->doctorId,
            'paciente_id' => $this->pacienteId,
            'fecha' => $this->fecha,
            'hora_inicio' => $this->horaInicio,
            'hora_fin' => $this->horaFin,
            'motivo' => $this->motivo,
            'estado' => $this->estado,
        ];
    }
}

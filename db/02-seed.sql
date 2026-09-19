INSERT INTO doctores (nombre, especialidad) VALUES
('Dra. Andrea López', 'Medicina General'),
('Dr. Carlos Méndez', 'Pediatría'),
('Dra. Sofía Ramírez', 'Cardiología');

INSERT INTO pacientes (nombre, telefono) VALUES
('Juan Pérez', '5555-1111'),
('María Gómez', '5555-2222'),
('Luis Fernández', '5555-3333');

INSERT INTO citas (doctor_id, paciente_id, fecha, hora_inicio, hora_fin, motivo, estado) VALUES
(1, 1, CURDATE(), '09:00:00', '09:30:00', 'Consulta general', 'confirmada'),
(2, 2, CURDATE(), '10:00:00', '10:30:00', 'Control pediátrico', 'pendiente');

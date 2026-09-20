# Evidencia - Control de Citas Médicas

**Estudiante:** Billy Eduardo Cardona López
**Carné:** 1890-20-15738
**Fecha:** 19 de septiembre de 2026

## 1. Requisitos cubiertos

| Requisito | Implementación |
|---|---|
| RQF-01, RQF-08 | `POST /api/citas`, validación de campos, fecha, horas y existencia de doctor/paciente |
| RQF-02, RQF-09, RQF-10 | FullCalendar con vistas mes/semana, detalle y colores por estado |
| RQF-03, RQNF-07 | Solapamiento validado en `CitaService` y `PdoCitaRepository`; responde 409 |
| RQF-04 | `eventDrop` ejecuta `PUT /api/citas/{id}` y revierte el evento si falla |
| RQF-05 | `PATCH /api/citas/{id}/estado` conserva la cita y su historial |
| RQF-06, RQF-07 | Filtros por doctor, paciente y rango; endpoints REST de citas, doctores y pacientes |
| RQNF-01, RQNF-02 | MySQL 8 en Docker con volumen `his_citas_data` y scripts de inicialización |
| RQNF-03, RQNF-04, RQNF-05, RQNF-06, RQNF-08 | JSON/códigos HTTP, capas, ramas/merges, UI responsive y este documento |

## 2. Levantar el entorno

```powershell
docker compose up -d
docker ps
php -S localhost:8000 -t public public/router.php
```

Contenedor verificado: `his_citas_mysql`, imagen `mysql:8.0`, estado `healthy`, puerto `3306`. MySQL usa el volumen `his_citas_data`.

Abrir `http://localhost:8000`.

## 3. Pruebas de API

Crear una cita y comprobar `201`:

```powershell
curl.exe -X POST http://localhost:8000/api/citas -H "Content-Type: application/json" -d '{"doctor_id":1,"paciente_id":1,"fecha":"2026-09-22","hora_inicio":"09:00","hora_fin":"09:30","motivo":"Control"}'
```

La respuesta exitosa tiene la forma `{"data":{"id":...,"estado":"pendiente",...}}`.

Comprobar conflicto y `409`:

```powershell
curl.exe -i -X POST http://localhost:8000/api/citas -H "Content-Type: application/json" -d '{"doctor_id":1,"paciente_id":2,"fecha":"2026-09-22","hora_inicio":"09:15","hora_fin":"09:45","motivo":"Choca"}'
```

La respuesta esperada es `HTTP/1.1 409` con `error: availability_overlap`.

Otros recorridos:

```powershell
curl.exe "http://localhost:8000/api/citas?doctor_id=1&desde=2026-09-01&hasta=2026-09-30"
curl.exe http://localhost:8000/api/doctores
curl.exe http://localhost:8000/api/pacientes
curl.exe -X PUT http://localhost:8000/api/citas/1 -H "Content-Type: application/json" -d '{"fecha":"2026-09-23","hora_inicio":"11:00","hora_fin":"11:30"}'
curl.exe -X PATCH http://localhost:8000/api/citas/1/estado -H "Content-Type: application/json" -d '{"estado":"confirmada"}'
```

## 4. Capturas de pantalla para adjuntar

- Calendario con eventos de los cuatro colores.
- Modal de detalle al seleccionar un evento.
- Reprogramación con drag & drop y evento actualizado.
- Formulario de creación y mensaje de éxito.
- Mensaje visual de conflicto de horario.

## 5. Flujo Git

Commits principales visibles en `git log --graph --all --oneline --decorate`:

```text
2d6c1f6 merge: integra interfaz FullCalendar interactiva
226e18e feat(RQF-01,RQF-02,RQF-04,RQF-06,RQF-09,RQF-10): integra FullCalendar con filtros y reprogramacion
7b63d1a merge: integra validacion de conflictos y estados
9c382dc feat(RQF-03,RQF-05,RQNF-07): protege estados cancelados y conflictos de horario
e8c49fc feat(RQF-07,RQF-08): API REST de citas con capas dominio/aplicacion/persistencia/presentacion
f42a7f4 merge: integra Docker MySQL y esquema UTF-8
8545a20 feat(RQNF-01,RQNF-02): configura charset utf8mb4 persistente para MySQL
2837ed5 feat(RQNF-01,RQNF-02): compose de MySQL con persistencia y esquema/semilla
```

- `feature/docker-mysql-schema` -> `8545a20` -> merge `f42a7f4`.
- `feature/api-rest-citas` -> PR #1 -> `e8c49fc` -> merge `3963e93`.
- `feature/validacion-conflictos-estados` -> `9c382dc` -> merge `7b63d1a`.
- `feature/fullcalendar-ui` -> `226e18e` -> merge `2d6c1f6`.

PR #1: https://github.com/billybc/examen-citas/pull/1

Comparar y abrir PR para las ramas restantes:

- https://github.com/billybc/examen-citas/compare/main...feature/docker-mysql-schema?expand=1
- https://github.com/billybc/examen-citas/compare/main...feature/validacion-conflictos-estados?expand=1
- https://github.com/billybc/examen-citas/compare/main...feature/fullcalendar-ui?expand=1

## 6. Nota de ejecución

`docker compose config --quiet` y el contenedor MySQL fueron validados correctamente. El lint de PHP no pudo ejecutarse en esta máquina porque PHP no está instalado localmente; ejecutar las pruebas de API y adjuntar sus capturas desde un entorno con PHP disponible.
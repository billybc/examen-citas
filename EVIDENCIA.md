# Evidencia — Control de Citas Médicas (Segundo Parcial)

**Estudiante:** Billy Eduardo Cardona López
**Carné:** 1890-20-15738
**Fecha:** 19 de septiembre de 2026

## 1. Backlog cubierto

| ID | Cubierto por |
|---|---|
| RQF-01 a RQF-10 | Ver `src/Application/CitaService.php` y `public/index.html` |
| RQNF-01 a RQNF-08 | Ver `docker-compose.yml`, capas en `src/`, y este documento |

## 2. Levantar el entorno

```bash
docker compose up -d
```

**[Pega aquí la salida de: docker ps]**

```
docker compose up -d
docker ps
```

## 3. Ejecutar la API

```bash
php -S localhost:8000 -t public public/router.php
```

Abrir en el navegador: http://localhost:8000

## 4. Pruebas de la API (curl)

**Crear cita (201):**
```bash
curl -X POST http://localhost:8000/api/citas \
  -H "Content-Type: application/json" \
  -d '{"doctor_id":1,"paciente_id":1,"fecha":"2026-09-22","hora_inicio":"09:00","hora_fin":"09:30","motivo":"Control"}'
```
**[Pega aquí la respuesta]**

**Conflicto de horario (409):**
```bash
curl -X POST http://localhost:8000/api/citas \
  -H "Content-Type: application/json" \
  -d '{"doctor_id":1,"paciente_id":2,"fecha":"2026-09-22","hora_inicio":"09:15","hora_fin":"09:45","motivo":"Choca"}'
```
**[Pega aquí la respuesta 409]**

**Listar citas (200):**
```bash
curl http://localhost:8000/api/citas
```
**[Pega aquí la respuesta]**

## 5. Capturas de pantalla

- [ ] Calendario con citas de colores según estado.
- [ ] Modal de detalle al hacer clic en una cita.
- [ ] Cita reprogramada con drag & drop (antes/después).
- [ ] Formulario de creación de cita.

## 6. Flujo Git

**[Pega aquí la salida de: git log --graph --all --oneline]**

- Rama `feature/docker-mysql-schema` → PR #__ → merge a main: commit __
- Rama `feature/api-rest-citas` → PR #__ → merge a main: commit __
- Rama `feature/validacion-conflictos-estados` → PR #__ → merge a main: commit __
- Rama `feature/fullcalendar-ui` → PR #__ → merge a main: commit __

## 7. Enlaces

- Repositorio: [pegar URL pública]
- PR 1 (docker-mysql-schema): [pegar URL]
- PR 2 (api-rest-citas): [pegar URL]
- PR 3 (validacion-conflictos-estados): [pegar URL]
- PR 4 (fullcalendar-ui): [pegar URL]

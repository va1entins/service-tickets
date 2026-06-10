# Service Tickets

Mini service ticket management system built with Symfony 7.4 + API Platform 4.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.3 |
| Framework | Symfony 7.4 |
| API | API Platform 4 |
| Database | PostgreSQL 16 |
| Cache / Queue | Redis 7 |
| Web server | Nginx 1.25 |
| Runtime | Docker / Docker Compose |

## Getting Started

```bash
git clone https://github.com/va1entins/service-tickets.git
cd service-tickets
docker compose up -d
make init
```

> The application will be available at **http://localhost:8080**

## Makefile Commands

```bash
make up          # start Docker containers
make down        # stop Docker containers
make down-v      # stop containers and remove volumes (reset database)
make restart     # restart all containers
make bash        # open bash in PHP container
make logs        # follow container logs
make migrate     # run database migrations
make fixtures    # load seed data
make init        # run migrations + load fixtures
make cache       # clear application cache
make routes      # list registered routes
make messenger   # list Messenger handlers
make phpstan     # run PHPStan static analysis (level 6)
```

## CI/CD

GitHub Actions pipeline runs automatically on every push and pull request to `main` and `develop`.

Pipeline jobs:
- **PHPStan** — static analysis level 6

## Postman Collection

Import `postman_collection.json` into Postman.

Set collection variables:
- `base_url` = `http://localhost:8080`
- `admin_user` = `admin`
- `admin_pass` = `admin123`

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/api/tickets` | List tickets (filtering, sorting, pagination) |
| `POST` | `/api/tickets` | Create a new ticket |
| `POST` | `/api/tickets/{id}/assign` | Assign a technician to a ticket |
| `POST` | `/api/tickets/{id}/status` | Change ticket status |
| `GET` | `/api/reports/technicians-performance` | Technician performance report |
| `GET` | `/api/docs` | Interactive OpenAPI documentation |

## Authentication

HTTP Basic Auth is used for all secured endpoints.

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | `ROLE_ADMIN` |
| `tech1` | `tech123` | `ROLE_TECHNICIAN` |

**Authorization rules:**
- Only `ROLE_ADMIN` can assign technicians
- `ROLE_TECHNICIAN` can only edit tickets assigned to themselves
- Report and read endpoints are accessible to both roles

## Filtering & Pagination

`GET /api/tickets` supports the following query parameters:

```
?status=NEW
?priority=HIGH
?serialNumber=SN-2024-001
?sort=createdAt&order=ASC
?page=1&limit=10
```

Available status values: `NEW`, `ASSIGNED`, `IN_PROGRESS`, `DONE`, `CANCELLED`  
Available priority values: `LOW`, `MEDIUM`, `HIGH`, `CRITICAL`

## Status Workflow

Allowed transitions:

```
NEW → ASSIGNED → IN_PROGRESS → DONE
                              ↘ CANCELLED (from any non-final status)
```

`DONE` and `CANCELLED` are final states — no further transitions are allowed.

## Architecture

The project follows **Layered Architecture** with **DDD elements**:

```
src/
├── Entity/            # Doctrine entities (Device, Technician, Ticket, TicketHistory)
├── Enum/              # Value Objects (TicketStatus, TicketPriority) with workflow logic
├── Repository/        # Doctrine repositories
├── Api/
│   ├── DTO/           # Input/Output DTOs
│   ├── Provider/      # Custom API Platform State Providers
│   └── Processor/     # Custom API Platform State Processors
├── Service/           # Application services
├── Message/           # Messenger messages
├── MessageHandler/    # Messenger handlers (e.g. ticket closed email simulation)
├── Security/Voter/    # Authorization voters
└── DataFixtures/      # Seed data
```

Key design decisions:
- No business logic in entities — workflow rules live in `TicketStatus` enum as Value Object
- No fat controllers — all logic delegated to Processors/Providers/Services
- N+1 free reports via optimized single native SQL query
- Ticket history created automatically on every status change

## Ports

| Service | Host port |
|---------|-----------|
| Nginx (HTTP) | `8080` |
| PostgreSQL | `5432` |
| Redis | `6379` |

## Docker Services

```
service_tickets_php        — PHP-FPM 8.3
service_tickets_nginx      — Nginx 1.25
service_tickets_postgres   — PostgreSQL 16
service_tickets_redis      — Redis 7
service_tickets_messenger  — Symfony Messenger worker
```

# HubSpot CRM Integration

A Laravel 12 service that synchronizes Contacts, Deals and their Associations with HubSpot CRM, and receives HubSpot webhooks to keep the local database in sync.

The HubSpot communication is encapsulated in a dedicated package — [`tambourine/hubspot-client`](https://github.com/JackeDev/hubspot-client) — so the application code never talks to the HubSpot REST API directly.

---

## Requirements

| Tool     | Version                    |
| -------- | -------------------------- |
| PHP      | ^8.2                       |
| Composer | ^2.x                       |
| SQLite   | bundled (default driver)   |
| HubSpot  | A Private App access token |

---

## Configuration

### 1. Install dependencies

```bash
composer install
```

The HubSpot client is pulled from a VCS repository declared in `composer.json`, so no extra step is needed.

### 2. Environment file

```bash
cp .env.example .env
php artisan key:generate
```

Then add the HubSpot credentials to `.env`:

```dotenv
# HubSpot
HUBSPOT_ACCESS_TOKEN=pat-xxxxxxxx-your-private-app-token
HUBSPOT_BASE_URL=https://api.hubapi.com/crm/v3
```

| Variable               | Required | Default                         | Description                                          |
| ---------------------- | -------- | ------------------------------- | ---------------------------------------------------- |
| `HUBSPOT_ACCESS_TOKEN` | ✅       | —                               | Private App token. Read from env only; never logged. |
| `HUBSPOT_BASE_URL`     | ❌       | `https://api.hubapi.com/crm/v3` | HubSpot CRM API base URL.                            |

> The token is resolved through `config('hubspot.token')` and consumed by the auth layer of the client package. To swap the auth mechanism (e.g. OAuth), only that layer changes — the rest of the app is unaffected.

### 3. Database

The project uses SQLite by default. Create the database file and run the migrations:

```bash
# Create the SQLite file (Linux/macOS)
touch database/database.sqlite
# Windows (PowerShell)
New-Item -ItemType File database/database.sqlite

php artisan migrate
```

To use MySQL/PostgreSQL instead, update the `DB_*` variables in `.env` before migrating.

### 4. Run the application

```bash
php artisan serve
```

The API is then available at `http://localhost:8000/api`.

---

## Running with Docker

The repository ships with a `Dockerfile` (PHP 8.3 CLI + Composer) and a `docker-compose.yml` that runs the app alongside a MySQL 8 database.

| Service | Container     | Image / Build | Ports (host → container) |
| ------- | ------------- | ------------- | ------------------------ |
| `app`   | `hubspot_app` | `Dockerfile`  | `8000 → 8000`            |
| `db`    | `hubspot_db`  | `mysql:8.0`   | `3307 → 3306`            |

### 1. Environment file

```bash
cp .env.example .env
```

Point the app at the `db` service (these values match `docker-compose.yml`):

```dotenv
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=hubspot
DB_USERNAME=laravel
DB_PASSWORD=laravel

# HubSpot
HUBSPOT_ACCESS_TOKEN=pat-xxxxxxxx-your-private-app-token
HUBSPOT_BASE_URL=https://api.hubapi.com/crm/v3
```

> `DB_HOST` is the Compose service name (`db`), not `localhost`. From your host machine the database is reachable on port **3307**.

### 2. Build and start the containers

```bash
docker compose up -d --build
```

### 3. Generate the app key and run migrations

Composer dependencies are installed during the image build; the remaining one-time setup runs inside the `app` container:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

The API is now available at `http://localhost:8000/api`.

### Useful commands

```bash
docker compose exec app php artisan test           # run the test suite
docker compose exec app php artisan l5-swagger:generate
docker compose logs -f app                         # tail application logs
docker compose down                                # stop and remove containers
docker compose down -v                             # also drop the MySQL volume
```

---

## API Documentation (Swagger)

OpenAPI docs are generated with [`darkaonline/l5-swagger`](https://github.com/DarkaOnLine/L5-Swagger) from PHP 8 attributes located in [`app/OpenApi/`](app/OpenApi/).

```bash
php artisan l5-swagger:generate
```

Then open: **`http://localhost:8000/api/documentation`**

---

## Endpoints

| Method | Path                            | Description                                                                   |
| ------ | ------------------------------- | ----------------------------------------------------------------------------- |
| `POST` | `/api/contacts`                 | Create a contact in HubSpot and store a local copy.                           |
| `POST` | `/api/deals`                    | Create a deal in HubSpot and store a local copy.                              |
| `POST` | `/api/associations`             | Associate a local contact with a local deal in HubSpot.                       |
| `POST` | `/api/webhooks/contact-updated` | Receive HubSpot `contact.propertyChange` events and update the local contact. |

### Request payloads

**`POST /api/contacts`**

```json
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "+1 555 123 4567"
}
```

`phone` is optional; `email` must be unique among local contacts.

**`POST /api/deals`**

```json
{
    "name": "Summer Vacation",
    "amount": 5000,
    "pipeline": "default",
    "stage": "qualified"
}
```

**`POST /api/associations`**

```json
{
    "contact_id": 1,
    "deal_id": 1
}
```

`contact_id` and `deal_id` are **local** database IDs; they are mapped to HubSpot IDs internally.

**`POST /api/webhooks/contact-updated`**

```json
{
    "event_id": "evt-788822595",
    "contact_id": "229957304227",
    "email": "john.updated@example.com",
    "phone": "+573022225565"
}
```

- `contact_id` is the HubSpot contact ID, matched against the local `client_id`.
- `email` and `phone` are each `required_without` the other — at least one must be present. `email` must be unique; `phone` must match the phone format.
- `event_id` is unique — duplicate events are rejected (idempotency).

### Error handling

HubSpot failures are caught and translated to a stable response. Typed exceptions thrown by the client package map to HTTP statuses:

| Exception                   | Meaning                         |
| --------------------------- | ------------------------------- |
| `AuthorizationException`    | Invalid/expired token (401)     |
| `RateLimitException`        | HubSpot rate limit (429)        |
| `ResourceNotFoundException` | Resource not found (404)        |
| `ValidationException`       | HubSpot rejected the data (422) |
| `GenericHubspotException`   | Any other HubSpot error         |

These surface to the client as `503 Service Unavailable` with a consistent JSON body, while input validation errors return `422` with field-level messages.

---

## Running the tests

```bash
php artisan test
# or
composer test
```

The suite covers Unit (FormRequest validation rules) and Feature (full HTTP flow with the HubSpot client mocked) tests for every endpoint, including happy paths and all error branches.

```bash
# Run a single suite
php artisan test tests/Feature/ContactControllerTest.php
php artisan test tests/Feature/Webhook
```

---

## Architecture

The application follows a layered design that keeps HTTP, business logic, persistence and the external integration cleanly separated.

```
HTTP Request
    │
    ▼
┌─────────────────────────────────────────────────────────────┐
│  FormRequest          validates & normalizes the input        │
│  (app/Http/Requests)                                          │
└─────────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────────────────────────┐
│  Controller           orchestrates, catches exceptions,       │
│  (app/Http/Controllers)   returns a Resource or error JSON    │
└─────────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────────────────────────────┐
│  Service              business logic: calls HubSpot, then     │
│  (app/Services)           persists the local copy             │
└──────────────┬───────────────────────────────┬──────────────┘
               │                                │
               ▼                                ▼
┌──────────────────────────┐   ┌──────────────────────────────┐
│  HubSpot Client Package  │   │  Repository                   │
│  (tambourine/hubspot-    │   │  (app/Repositories)           │
│   client)                │   │      │                        │
│   DTOs · Services ·      │   │      ▼                        │
│   typed Exceptions       │   │  Eloquent Model + SQLite      │
└──────────────────────────┘   └──────────────────────────────┘
```

### Layers

| Layer            | Location                    | Responsibility                                                                                |
| ---------------- | --------------------------- | --------------------------------------------------------------------------------------------- |
| **Routes**       | `routes/api.php`            | Map URLs to controllers; attach webhook logging middleware.                                   |
| **Requests**     | `app/Http/Requests`         | Validation rules (incl. uniqueness/existence and idempotency).                                |
| **Controllers**  | `app/Http/Controllers`      | Thin orchestration; exception-to-HTTP mapping via the `HandleExceptions` trait.               |
| **Services**     | `app/Services`              | Business logic: sync with HubSpot first, then persist locally.                                |
| **Repositories** | `app/Repositories`          | Data access abstraction over Eloquent models.                                                 |
| **Resources**    | `app/Http/Resources`        | API response shaping.                                                                         |
| **Models**       | `app/Models`                | Eloquent entities (`Contact`, `Deal`, `Association`, `WebhookContactEvent`).                  |
| **Client pkg**   | `tambourine/hubspot-client` | All HubSpot HTTP calls, DTOs and typed exceptions — the only code that knows the HubSpot API. |
| **OpenApi**      | `app/OpenApi`               | Swagger attribute definitions (info, endpoints, schemas).                                     |

### Why a separate client package?

The HubSpot REST contract lives entirely inside `tambourine/hubspot-client`. The app speaks to it through service classes and DTOs, so:

- The auth strategy (token today, OAuth tomorrow) can be replaced without touching the app.
- The HubSpot API surface can evolve independently and be versioned/reused across projects.
- Error handling is centralized as a typed exception hierarchy.

### Data model

| Table                    | Purpose                                                                                                      |
| ------------------------ | ------------------------------------------------------------------------------------------------------------ |
| `contacts`               | Local copy of HubSpot contacts. `client_id` stores the HubSpot ID; `(client_id, client_provider)` is unique. |
| `deals`                  | Local copy of HubSpot deals.                                                                                 |
| `associations`           | Links a local contact to a local deal.                                                                       |
| `webhook_contact_events` | Ledger of processed webhook events. `event_id` is unique to guarantee idempotent processing.                 |

### Logging

Two dedicated daily log channels keep operational noise out of the main log:

| Channel    | File                        | Level   | Used for                                                                                                                                                                              |
| ---------- | --------------------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `hubspot`  | `storage/logs/hubspot.log`  | `error` | HubSpot API failures (endpoint, method, status, response body — **never** the token).                                                                                                 |
| `webhooks` | `storage/logs/webhooks.log` | `info`  | Every inbound webhook attempt (IP, method, payload) and the response status — logged by `LogWebhookRequest` middleware **before** validation, so even rejected requests are recorded. |

---

## Project layout

```
app/
├── Http/
│   ├── Controllers/        Contact, Deal, Association, Webhook\Contact
│   ├── Middleware/         LogWebhookRequest
│   ├── Requests/           Create*/Update* FormRequests
│   └── Resources/          *Resource response shapers
├── Models/                 Contact, Deal, Association, WebhookContactEvent
├── Repositories/           Data-access layer
├── Services/               Business logic + HubSpot orchestration
├── Traits/                 HandleExceptions, ErrorResponses
└── OpenApi/                Swagger attribute definitions

config/
├── hubspot.php             (published from the client package)
└── logging.php             hubspot + webhooks channels

database/migrations/        contacts, deals, associations, webhook_contact_events
routes/api.php              API route definitions
tests/                      Unit (Requests) + Feature (full HTTP flow)
```

## License

MIT © Jackeline Hernandez

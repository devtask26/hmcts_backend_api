# HMCTS Backend API

A Laravel-based REST API for task management.

## Overview

This project provides the ability to manage user's tasks.

- Domain driven development
- Separation of concerns
- Form Requests Validation
- Traced ID logging
- Unit & Integration Testing
- Dockerized environment (MySQL, Redis, and Nginx)

## Quick Start

### Prerequisites
- Docker & Docker Compose
- Composer (for local development)
- Node.js & NPM (for frontend assets)

### 1. Clone and Setup
```bash
git clone <repository-url>
cd hmcts_system/hmcts_backend_api
```

### 2. Start Docker Services
```bash
docker-compose up --buld -d
```

### 3. Install Dependencies
```bash
# Enter the app container
docker-compose exec (app_container_name) bash

# Install PHP dependencies
composer install

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Create Users (Required Before Task Creation)
```bash
# Add a user using the Makefile from outside the container
make add-user NAME="John Doe" EMAIL="john@example.com"

# You can add multiple users
make add-user NAME="Jane Smith" EMAIL="jane@example.com"
```

**Important**: You must create at least one user before creating tasks, as tasks require a valid `user_id` that references an existing user in the database.
```

### 4. Database Operations
```bash
# Run migrations
php artisan migrate

# Fresh migration (will delete data)
php artisan migrate:fresh

# Create new migration
php artisan make:migration create_tasks_table

# Seed database
php artisan db:seed
```

### 5. Executing tests
```bash
# Enter app container
docker-compose exec (app_container_name) bash

# Execute test
php artisan test
```

## API Endpoints

### Base URL (may depend on docker setup)
```
http://localhost:8081/api
```

### Headers
```
X-Trace-ID: <optional-trace-id> (for tracing logs)
Content-Type: application/json
```

### Tasks API

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/tasks` | Get all tasks |
| `GET` | `/tasks/{id}` | Get specific task |
| `POST` | `/tasks` | Create new task |
| `PUT` | `/tasks/{id}` | Update existing task |
| `DELETE` | `/tasks/{id}` | Delete task |


## Request/Response Examples
### Create Task
```bash
POST /api/tasks
Content-Type: application/json

{
  "user_id": 1,
  "title": "Complete project documentation",
  "description": "Write comprehensive README and API docs",
  "due_date": "2026-09-15T10:00:00Z",
  "caseNumber": "CASE-12345",
  "status": "pending"
}
```

#### Response
```json
{
  "id": 1,
  "user_id": 1,
  "title": "Complete project documentation",
  "description": "Write comprehensive README and API docs",
  "due_date": "2026-09-15T10:00:00Z",
  "caseNumber": "CASE-12345",
  "status": "pending",
  "created_at": "2026-04-06T17:00:00Z",
  "updated_at": "2026-04-06T17:00:00Z"
}
```
#### Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."],
    "user_id": ["The user id field is required.", "The selected user id is invalid."]
  }
}
```

**Note**: The `user_id` must reference an existing user in the database. Use `make add-user` to create users first.

### Get All Tasks
```bash
GET /api/tasks
```

#### Response
```json
[
  {
    "id": 1,
    "user_id": 1,
    "title": "Complete project documentation",
    "description": "Write comprehensive README and API docs",
    "due_date": "2026-09-15T10:00:00Z",
    "caseNumber": "CASE-12345",
    "status": "pending",
    "created_at": "2026-04-06T17:00:00Z",
    "updated_at": "2026-04-06T17:00:00Z"
  },
  {
    "id": 2,
    "user_id": 1,
    "title": "Review code changes",
    "description": "Review pull requests and provide feedback",
    "due_date": "2026-09-16T14:00:00Z",
    "caseNumber": "CASE-12346",
    "status": "in_progress",
    "created_at": "2026-04-06T17:30:00Z",
    "updated_at": "2026-04-06T17:30:00Z"
  }
]
```

### Get Specific Task
```bash
GET /api/tasks/1
```

#### Response
```json
{
  "id": 1,
  "user_id": 1,
  "title": "Complete project documentation",
  "description": "Write comprehensive README and API docs",
  "due_date": "2026-09-15T10:00:00Z",
  "caseNumber": "CASE-12345",
  "status": "pending",
  "created_at": "2026-04-06T17:00:00Z",
  "updated_at": "2026-04-06T17:00:00Z"
}
```

### Update Task
```bash
PUT /api/tasks/1
Content-Type: application/json

{
  "title": "Updated project documentation",
  "description": "Write comprehensive README and API docs with examples",
  "due_date": "2026-09-17T10:00:00Z",
  "caseNumber": "CASE-12345-UPDATED",
  "status": "in_progress"
}
```

#### Response
```json
{
  "id": 1,
  "user_id": 1,
  "title": "Updated project documentation",
  "description": "Write comprehensive README and API docs with examples",
  "due_date": "2026-09-17T10:00:00Z",
  "caseNumber": "CASE-12345-UPDATED",
  "status": "in_progress",
  "created_at": "2026-04-06T17:00:00Z",
  "updated_at": "2026-04-06T18:00:00Z"
}
```
#### Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."],
    "user_id": ["The user id field is required."]
  }
}
```

### Delete Task
```bash
DELETE /api/tasks/1
```

#### Response
```json
{
  "success": true,
  "message": "Task deleted successfully"
}
```

## Testing

### Test Coverage
The project includes comprehensive tests for:
- TaskRepository (CRUD operations)
- TaskService (business logic)
- TaskController (HTTP endpoints)
- Form Request Validation
- Middleware (Trace ID)
- Integration workflows

## Monitoring & Logging

### Trace ID Tracking
Every request includes a unique trace ID for tracking:
- **Generated automatically** if not provided
- **Propagated through logs** for request tracing
- **Returned in response headers** for client tracking

### LogDeck Dashboard
Access the LogDeck monitoring interface:
```
http://localhost:8121
```

## Project Structure

```
hmcts_backend_api/
├── application/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/          # API Controllers
│   │   │   ├── Middleware/          # Custom Middleware
│   │   ├── Models/                   # Eloquent Models
│   │   ├── Repositories/             # Data Access Layer
│   │   ├── Services/                 # Business Logic Layer
│   │   └── Traits/                   # Reusable Traits
│   ├── tests/
│   │   ├── Unit/                     # Unit Tests
│   │   ├── Feature/                  # Feature Tests
│   │   └── Integration/              # Integration Tests
│   ├── database/
│   │   ├── migrations/               # Database Migrations
│   │   ├── factories/                # Model Factories
│   │   └── seeders/                  # Database Seeders
│   └── routes/
│       ├── api.php                   # API Routes
│       └── web.php                   # Web Routes
├── docker/
│   ├── nginx/                        # Nginx Configuration
│   └── php/                          # PHP Dockerfile
├── docker-compose.yml                # Docker Services
└── README.md                         # This File
```

## API Workflow

### Typical Request Flow
- **Request** → Nginx → Laravel App
- **Middleware** → Trace ID assignment
- **Controller** → Request validation
- **Service** → Business logic
- **Repository** → Database operations
- **Response** → JSON with trace ID

### Error Handling
- **Validation Errors**: 422 with detailed error messages
- **Not Found**: 404 with clear error message
- **Server Errors**: 500 with generic error message
- **All errors** include trace ID for debugging

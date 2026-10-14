# Employee Management API

A small RESTful API built with **PHP 8.2** and **Laravel 10.x** for managing employees. Employees are held in an in-memory collection. There is no database.

> [!IMPORTANT]
> **Data does not persist between requests.** As the brief requires, employees live in an in-memory collection. PHP rebuilds the application on every HTTP request, so each request starts from the same three seeded employees. A `POST` succeeds and returns `201`, but a later `GET` won't include the new employee. This is intentional. See [Data persistence](#data-persistence-key-assumption) for details and how to persist data with Laravel's cache instead.

## Endpoints

| Method | URI                       | Description            | Success          |
|--------|---------------------------|------------------------|------------------|
| GET    | `/api/employees`          | List all employees     | `200 OK`         |
| GET    | `/api/employees/{id}`     | Retrieve one employee  | `200 OK`         |
| POST   | `/api/employees`          | Create an employee     | `201 Created`    |
| DELETE | `/api/employees/{id}`     | Delete an employee     | `204 No Content` |

### Employee resource

```json
{
  "id": 1,
  "firstName": "Jane",
  "lastName": "Doe",
  "email": "jane.doe@example.com"
}
```

Successful responses wrap the payload in a `data` key (`{"data": {...}}` or `{"data": [...]}`).

### Validation rules (POST)

| Field       | Rules                                                                       |
|-------------|-----------------------------------------------------------------------------|
| `firstName` | required, string, max 100 characters                                        |
| `lastName`  | required, string, max 100 characters                                        |
| `email`     | required, valid email, max 255 characters, unique across employees (case-insensitive) |

Unknown fields (including `id`) are ignored; IDs are always assigned by the server.

### Status codes and error responses

| Status | When                                                                   | Body                                                        |
|--------|------------------------------------------------------------------------|-------------------------------------------------------------|
| 200    | Employee(s) returned                                                   | `{"data": ...}`                                             |
| 201    | Employee created (`Location` header points at the new resource)        | `{"data": {...}}`                                           |
| 204    | Employee deleted                                                       | empty                                                       |
| 400    | Request body is not valid JSON                                         | `{"message": "The request body is not valid JSON."}`        |
| 404    | Employee ID does not exist                                             | `{"message": "Employee with ID 99 not found."}`             |
| 404    | Unknown route or invalid ID (e.g. `abc`, `0`, `-1`, `001`, oversized)  | `{"message": "..."}`                                        |
| 405    | Unsupported method (e.g. `PUT /api/employees/1`)                       | `{"message": "..."}`                                        |
| 422    | Validation failed                                                      | `{"message": "...", "errors": {"field": ["..."]}}`          |

API routes always return JSON errors, even if the client does not send an `Accept: application/json` header.

## Running the application

The app is seeded with three sample employees (IDs 1 to 3).

### Option A: Docker (no local PHP required)

```bash
docker compose up --build
```

The API is then available at `http://localhost:8000/api/employees`.

For development, use watch mode so code changes appear without restarting:

```bash
docker compose up --build --watch
```

Edits to `app/`, `routes/`, `config/`, `tests/` and similar folders are synced into the running container and take effect on the next request. Changes to `composer.json`, `composer.lock` or the `Dockerfile` trigger an automatic rebuild. Press `Ctrl+C` to stop.

### Option B: Local PHP

Requirements: PHP 8.2+, Composer 2, and the PHP extensions Laravel needs (`mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`).

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

The API is then available at `http://localhost:8000/api/employees`.

## Trying the API

Start the application first (see above). All requests go to the base URL `http://localhost:8000/api`. You can call the API with any HTTP client. The sections below cover **curl** (command line) and **Postman** (GUI).

> **Note:** Data is kept in memory and resets on every request (see [Data persistence](#data-persistence-key-assumption)). A create request returns `201` with the new employee, but a later `GET` for that ID returns `404`. Every request starts from the three seeded employees (IDs 1 to 3).

### Using curl

curl ships with macOS, Linux, Git Bash and Windows 10+. Open a terminal **separate from the one running the server** and run the commands below. Add `-i` to any command to also see the status code and response headers.

The examples use bash syntax, which works in macOS/Linux terminals, Git Bash and WSL. For Windows Command Prompt and PowerShell, see [Windows users](#windows-users).

#### List all employees

```bash
curl -i http://localhost:8000/api/employees
```

```
HTTP/1.1 200 OK

{"data":[{"id":1,"firstName":"Jane","lastName":"Doe","email":"jane.doe@example.com"},
         {"id":2,"firstName":"John","lastName":"Smith","email":"john.smith@example.com"},
         {"id":3,"firstName":"Maria","lastName":"Garcia","email":"maria.garcia@example.com"}]}
```

#### Retrieve an employee by ID

```bash
curl -i http://localhost:8000/api/employees/1
```

```
HTTP/1.1 200 OK

{"data":{"id":1,"firstName":"Jane","lastName":"Doe","email":"jane.doe@example.com"}}
```

A missing ID returns a 404:

```bash
curl -i http://localhost:8000/api/employees/999
```

```
HTTP/1.1 404 Not Found

{"message":"Employee with ID 999 not found."}
```

#### Create an employee

Send a JSON body with the `Content-Type: application/json` header:

```bash
curl -i -X POST http://localhost:8000/api/employees \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"firstName":"Alex","lastName":"Turner","email":"alex.turner@example.com"}'
```

```
HTTP/1.1 201 Created
Location: http://localhost:8000/api/employees/4

{"data":{"id":4,"firstName":"Alex","lastName":"Turner","email":"alex.turner@example.com"}}
```

Invalid data returns a 422 with an error message for each field:

```bash
curl -i -X POST http://localhost:8000/api/employees \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"firstName":"","email":"not-an-email"}'
```

```
HTTP/1.1 422 Unprocessable Content

{"message":"The first name field is required. (and 2 more errors)",
 "errors":{"firstName":["The first name field is required."],
           "lastName":["The last name field is required."],
           "email":["The email address field must be a valid email address."]}}
```

A duplicate email (for example `jane.doe@example.com`) returns a 422 with `"An employee with this email address already exists."`.

#### Delete an employee

```bash
curl -i -X DELETE http://localhost:8000/api/employees/1
```

```
HTTP/1.1 204 No Content
```

Deleting an ID that doesn't exist returns `404` with `{"message":"Employee with ID 999 not found."}`.

#### Windows users

**Command Prompt:** use one line and escape the inner double quotes:

```bat
curl -i -X POST http://localhost:8000/api/employees -H "Content-Type: application/json" -H "Accept: application/json" -d "{\"firstName\":\"Alex\",\"lastName\":\"Turner\",\"email\":\"alex.turner@example.com\"}"
```

**PowerShell:** `curl` is an alias for `Invoke-WebRequest` in Windows PowerShell, so use `Invoke-RestMethod` instead:

```powershell
# List
Invoke-RestMethod http://localhost:8000/api/employees

# Create
$body = @{ firstName = 'Alex'; lastName = 'Turner'; email = 'alex.turner@example.com' } | ConvertTo-Json
Invoke-RestMethod -Method Post -Uri http://localhost:8000/api/employees -ContentType 'application/json' -Body $body

# Delete
Invoke-RestMethod -Method Delete -Uri http://localhost:8000/api/employees/1
```

### Using Postman

[Postman](https://www.postman.com/downloads/) is a desktop app for sending HTTP requests. With the application running:

1. Open Postman and click **New → HTTP** (or the **+** tab) to create a request.
2. Pick the method from the dropdown and enter the URL:

   | Action              | Method   | URL                                         |
   |---------------------|----------|---------------------------------------------|
   | List employees      | `GET`    | `http://localhost:8000/api/employees`       |
   | Get one employee    | `GET`    | `http://localhost:8000/api/employees/1`     |
   | Create an employee  | `POST`   | `http://localhost:8000/api/employees`       |
   | Delete an employee  | `DELETE` | `http://localhost:8000/api/employees/1`     |

3. On the **Headers** tab, add `Accept: application/json`. This is optional because the API always returns JSON errors, but it's good practice.
4. **For create (`POST`):** open the **Body** tab, select **raw**, choose **JSON** from the format dropdown (this sets `Content-Type: application/json` automatically), and enter:

   ```json
   {
     "firstName": "Alex",
     "lastName": "Turner",
     "email": "alex.turner@example.com"
   }
   ```

5. Click **Send**. The response panel shows the status code (for example `201 Created`), the JSON body, and the response headers, including `Location` for a newly created employee.

To try the error cases, change the body to an invalid email or remove a field to get a `422`, or request an ID like `999` to get a `404`.

**Tip:** to avoid retyping the host, create a Postman environment with a variable `baseUrl = http://localhost:8000/api`. Then use URLs like `{{baseUrl}}/employees`.

## Running the tests

```bash
# Docker
docker compose run --rm app php artisan test

# Local PHP
php artisan test
```

The suite covers:
- **Feature tests** (`tests/Feature/EmployeeApiTest.php`): every endpoint end to end, including successful requests, validation failures (missing fields, invalid email, overlong names, duplicate email), malformed JSON, missing employees, deletion behavior, invalid IDs and unsupported methods.
- **Unit tests** (`tests/Unit`): the in-memory repository and the employee service, tested in isolation.

## Project structure

```
app/
├── Entities/Employee.php                         Immutable value object (readonly class)
├── Exceptions/EmployeeNotFoundException.php      Domain exception, renders a 404 JSON response
├── Exceptions/Handler.php                        Forces JSON errors for /api/* routes
├── Http/Controllers/Api/EmployeeController.php   Thin HTTP layer
├── Http/Requests/StoreEmployeeRequest.php        Input validation
├── Http/Resources/EmployeeResource.php           Response shape
├── Providers/AppServiceProvider.php              Binds the repository interface
├── Repositories/Contracts/EmployeeRepositoryInterface.php
├── Repositories/InMemoryEmployeeRepository.php   In-memory data store
├── Rules/UniqueEmployeeEmail.php                 In-memory "unique" validation rule
└── Services/EmployeeService.php                  Business operations
routes/api.php                                    Route definitions
tests/Feature, tests/Unit                         Automated tests
Dockerfile, docker-compose.yml                    Container setup (run, test, watch)
```

## Design decisions

- **Layering.** A request flows `Route → Controller → FormRequest → Service → Repository`, and the result is shaped by an API Resource. The controller only deals with HTTP: input, status codes and headers. `EmployeeService` holds the business operations and raises domain errors. The repository is the only class that knows how employees are stored.
- **Repository behind an interface.** `EmployeeRepositoryInterface` is bound to `InMemoryEmployeeRepository` as a singleton in `AppServiceProvider`. Switching storage (cache, database, external API) only means writing a new implementation and changing that one binding. Controllers, services and tests stay the same.
- **Plain entity instead of an Eloquent model.** There is no database, so `Employee` is a small `readonly` class in `app/Entities` rather than a model in `app/Models`. That avoids implying Eloquent behavior that doesn't exist.
- **Laravel-native validation.** A `FormRequest` handles validation, so failures produce Laravel's standard 422 response automatically. Email uniqueness is checked by a custom `ValidationRule` that asks the repository, because the built-in `unique` rule requires a database.
- **Domain exception for "not found".** The service throws `EmployeeNotFoundException`, which renders its own 404 JSON response. This keeps not-found handling consistent for both `show` and `destroy` without `if` checks in the controller.
- **Route constraints.** The `{employee}` parameter only matches positive integers without leading zeros, up to 18 digits so the value always fits in a PHP integer. Anything else (`abc`, `0`, `-1`, `001`, oversized numbers) returns 404 at the routing layer, before reaching the controller.
- **Malformed JSON gets a 400.** `StoreEmployeeRequest` rejects an unparseable JSON body with `400 Bad Request`. Otherwise Laravel would treat the body as empty and report every field as missing, which hides the real problem.
- **REST conventions.** `Route::apiResource(...)->only([...])` exposes exactly the four required operations. `POST` returns `201` with a `Location` header, and `DELETE` returns `204` with no body.

## Assumptions

### Data persistence (key assumption)

> [!IMPORTANT]
> **Employee data is held in memory and resets on every HTTP request.**

The brief asks for a static or in-memory collection with no database. The repository is bound as a singleton, so all classes share one collection within one application instance. However, PHP doesn't keep the application in memory between HTTP requests. Each request:

1. boots a fresh Laravel application,
2. creates `InMemoryEmployeeRepository`, seeded with the three sample employees,
3. handles the request (for example, adds a new employee and returns `201`),
4. then discards everything in memory, including any changes.

**What this means in practice:**

- A `POST` or `DELETE` made with curl or Postman succeeds and returns the correct response. The next request still sees the original three employees.
- **The automated tests do verify the full behavior.** All requests within one test share a single application instance, so flows like create → fetch → list → delete are fully exercised (see `tests/Feature/EmployeeApiTest.php`).

#### How to persist data between requests with Laravel Cache

Data can survive between requests without a database by storing the collection in Laravel's cache. The default `file` cache driver writes to `storage/framework/cache`. Thanks to the repository interface, this means adding **one class and changing one line**. The controller, service, validation rule and tests stay the same.

**1. Add `app/Repositories/CacheEmployeeRepository.php`.** It reuses the in-memory repository's logic and keeps an instance of it in the cache. Writes take a cache lock so two simultaneous requests can't overwrite each other's changes.

```php
<?php

namespace App\Repositories;

use App\Entities\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class CacheEmployeeRepository implements EmployeeRepositoryInterface
{
    private const CACHE_KEY = 'employees';

    public function all(): array
    {
        return $this->store()->all();
    }

    public function find(int $id): ?Employee
    {
        return $this->store()->find($id);
    }

    public function findByEmail(string $email): ?Employee
    {
        return $this->store()->findByEmail($email);
    }

    public function create(array $attributes): Employee
    {
        return $this->write(fn (InMemoryEmployeeRepository $store) => $store->create($attributes));
    }

    public function delete(int $id): bool
    {
        return $this->write(fn (InMemoryEmployeeRepository $store) => $store->delete($id));
    }

    // Loads the collection from the cache, seeding it on first use.
    private function store(): InMemoryEmployeeRepository
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => new InMemoryEmployeeRepository);
    }

    // Applies a change and saves the collection back while holding a lock.
    private function write(callable $change): mixed
    {
        return Cache::lock(self::CACHE_KEY.':lock', 5)->block(5, function () use ($change) {
            $store = $this->store();
            $result = $change($store);
            Cache::forever(self::CACHE_KEY, $store);

            return $result;
        });
    }
}
```

**2. Swap the binding in `app/Providers/AppServiceProvider.php`.** A commented-out line is already there:

```php
$this->app->singleton(EmployeeRepositoryInterface::class, CacheEmployeeRepository::class);
```

**3. Reset to the seed data at any time:**

```bash
php artisan cache:clear                          # local PHP
docker compose exec app php artisan cache:clear  # Docker
```

Notes:

- The feature tests keep passing unchanged. `phpunit.xml` sets `CACHE_DRIVER=array`, so each test still starts from fresh seed data.
- In Docker, the cache lives inside the container, so data persists until the container is recreated.
- I didn't use this approach by default because the brief asks for an in-memory collection, and cache files are a step toward external storage. It's documented here so persistence is a single binding change if it's ever needed.

### Other assumptions

- IDs are integers assigned by the server and are never reused after deletion.
- Email addresses must be unique, compared case-insensitively.
- JSON fields use camelCase (`firstName`, `lastName`), matching the example payload in the brief.
- Authentication, pagination and filtering were out of scope.
- The standard Laravel skeleton files were left untouched and are unused by the API: the `User` model, default migrations, Sanctum config and the welcome page at `/`. Keeping them avoids diverging from a stock Laravel 10 install.
- **Laravel 10 and security advisories.** Laravel 10 is past its end of life, and Composer 2.9+ refuses by default to install packages that have open security advisories. Because the brief requires Laravel 10.x, `composer.json` sets `config.audit.block-insecure` to `false`. The advisories cover features this API doesn't use (sending email, signed URLs and debug pages; debug output is turned off in the Docker setup). A production service should be on a supported Laravel version.

## Possible extensions

- **Update endpoint.** Add `update` to the `only([...])` list in `routes/api.php`, then add an `UpdateEmployeeRequest`, `EmployeeController::update()`, `EmployeeService::update()` and `EmployeeRepositoryInterface::update()`. Placeholder comments mark each of these spots in the code.
- Pagination for the list endpoint once the data set grows.

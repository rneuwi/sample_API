<?php

namespace App\Repositories;

use App\Entities\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;

/**
 * Stores employees in a plain PHP array for the lifetime of the application
 * instance. The collection is seeded with sample data on construction.
 *
 * Lifetime: PHP boots a fresh application for every HTTP request, so changes
 * made here (creates/deletes) are discarded once the response is sent and the
 * next request starts again from the seed data. Within a single application
 * instance - such as a feature test - changes are visible to later calls.
 *
 * Persisting between requests without a database: add a
 * CacheEmployeeRepository that implements EmployeeRepositoryInterface and keeps
 * an instance of this class in Laravel's cache. Reads load it with
 * Cache::rememberForever('employees', fn () => new InMemoryEmployeeRepository()).
 * Writes take a Cache::lock(), apply the change to the loaded instance, and
 * store it back with Cache::forever(). Then swap the binding in
 * AppServiceProvider. See "Data persistence" in the README for a full example.
 */
class InMemoryEmployeeRepository implements EmployeeRepositoryInterface
{
    /**
     * @var array<int, Employee>
     */
    private array $employees = [];

    private int $nextId = 1;

    /**
     * @param  list<array{firstName: string, lastName: string, email: string}>|null  $seed
     */
    public function __construct(?array $seed = null)
    {
        foreach ($seed ?? self::defaultSeed() as $attributes) {
            $this->create($attributes);
        }
    }

    public function all(): array
    {
        return array_values($this->employees);
    }

    public function find(int $id): ?Employee
    {
        return $this->employees[$id] ?? null;
    }

    public function findByEmail(string $email): ?Employee
    {
        $needle = mb_strtolower($email);

        foreach ($this->employees as $employee) {
            if (mb_strtolower($employee->email) === $needle) {
                return $employee;
            }
        }

        return null;
    }

    public function create(array $attributes): Employee
    {
        $employee = new Employee(
            id: $this->nextId++,
            firstName: $attributes['firstName'],
            lastName: $attributes['lastName'],
            email: $attributes['email'],
        );

        $this->employees[$employee->id] = $employee;

        return $employee;
    }

    public function delete(int $id): bool
    {
        if (! isset($this->employees[$id])) {
            return false;
        }

        unset($this->employees[$id]);

        return true;
    }

    /**
     * @return list<array{firstName: string, lastName: string, email: string}>
     */
    private static function defaultSeed(): array
    {
        return [
            ['firstName' => 'Jane', 'lastName' => 'Doe', 'email' => 'jane.doe@example.com'],
            ['firstName' => 'John', 'lastName' => 'Smith', 'email' => 'john.smith@example.com'],
            ['firstName' => 'Maria', 'lastName' => 'Garcia', 'email' => 'maria.garcia@example.com'],
        ];
    }
}

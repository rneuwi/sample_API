<?php

namespace App\Services;

use App\Entities\Employee;
use App\Exceptions\EmployeeNotFoundException;
use App\Repositories\Contracts\EmployeeRepositoryInterface;

/**
 * Application-level operations on employees. Controllers talk to this
 * service rather than to the repository directly, keeping HTTP concerns
 * separate from business rules and storage.
 */
class EmployeeService
{
    public function __construct(private readonly EmployeeRepositoryInterface $employees) {}

    /**
     * @return list<Employee>
     */
    public function list(): array
    {
        return $this->employees->all();
    }

    /**
     * @throws EmployeeNotFoundException
     */
    public function get(int $id): Employee
    {
        return $this->employees->find($id) ?? throw new EmployeeNotFoundException($id);
    }

    /**
     * @param  array{firstName: string, lastName: string, email: string}  $attributes
     */
    public function create(array $attributes): Employee
    {
        return $this->employees->create($attributes);
    }

    /**
     * @throws EmployeeNotFoundException
     */
    public function delete(int $id): void
    {
        if (! $this->employees->delete($id)) {
            throw new EmployeeNotFoundException($id);
        }
    }

    // Future: an update endpoint would add an update(int $id, array $attributes)
    // method here that throws EmployeeNotFoundException for unknown IDs.
}

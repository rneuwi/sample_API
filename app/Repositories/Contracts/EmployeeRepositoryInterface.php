<?php

namespace App\Repositories\Contracts;

use App\Entities\Employee;

interface EmployeeRepositoryInterface
{
    /**
     * @return list<Employee>
     */
    public function all(): array;

    public function find(int $id): ?Employee;

    public function findByEmail(string $email): ?Employee;

    /**
     * @param  array{firstName: string, lastName: string, email: string}  $attributes
     */
    public function create(array $attributes): Employee;

    public function delete(int $id): bool;

    // Future: an update endpoint would add a method such as
    // public function update(int $id, array $attributes): ?Employee;
}

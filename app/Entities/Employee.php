<?php

namespace App\Entities;

/**
 * Plain value object representing an employee.
 *
 * Deliberately not an Eloquent model: the application has no database and
 * employees are held in memory by the repository layer.
 */
final readonly class Employee
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $email,
    ) {}
}

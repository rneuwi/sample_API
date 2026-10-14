<?php

namespace App\Rules;

use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * In-memory equivalent of Laravel's database "unique" rule.
 */
class UniqueEmployeeEmail implements ValidationRule
{
    public function __construct(private readonly EmployeeRepositoryInterface $employees) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && $this->employees->findByEmail($value) !== null) {
            $fail('An employee with this :attribute already exists.');
        }
    }
}

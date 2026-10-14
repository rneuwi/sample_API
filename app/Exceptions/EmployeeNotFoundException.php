<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class EmployeeNotFoundException extends RuntimeException
{
    public function __construct(public readonly int $employeeId)
    {
        parent::__construct("Employee with ID {$employeeId} not found.");
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], Response::HTTP_NOT_FOUND);
    }
}

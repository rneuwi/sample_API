<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Validates the {employee} route parameter for actions that target a single
 * employee (show, destroy).
 */
class EmployeeIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validate the route parameter only, never the request body.
     *
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return ['employee' => $this->route('employee')];
    }

    /**
     * IDs must be positive integers without leading zeros, capped at 18 digits
     * so they always fit in a PHP int.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'employee' => ['required', 'regex:/^[1-9][0-9]{0,17}$/'],
        ];
    }

    /**
     * A malformed ID can never identify an employee, so respond with 404
     * rather than 422, matching the response for an ID that doesn't exist.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new NotFoundHttpException('Employee ID must be a positive integer.');
    }
}

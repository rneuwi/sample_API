<?php

namespace App\Http\Requests;

use App\Rules\UniqueEmployeeEmail;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reject unparseable JSON with a 400 instead of reporting every field as
     * missing, which would hide the real problem from the client.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->isJson() || $this->getContent() === '') {
            return;
        }

        json_decode($this->getContent());

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestHttpException('The request body is not valid JSON.');
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', app(UniqueEmployeeEmail::class)],
        ];
    }

    /**
     * Human-readable field names used in validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'firstName' => 'first name',
            'lastName' => 'last name',
            'email' => 'email address',
        ];
    }
}

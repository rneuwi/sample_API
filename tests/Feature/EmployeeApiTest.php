<?php

namespace Tests\Feature;

use Tests\TestCase;

class EmployeeApiTest extends TestCase
{
    private const VALID_PAYLOAD = [
        'firstName' => 'Alex',
        'lastName' => 'Turner',
        'email' => 'alex.turner@example.com',
    ];

    public function test_index_returns_all_employees(): void
    {
        $this->getJson('/api/employees')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'firstName', 'lastName', 'email']]])
            ->assertJsonPath('data.0.email', 'jane.doe@example.com');
    }

    public function test_show_returns_an_employee(): void
    {
        $this->getJson('/api/employees/1')
            ->assertOk()
            ->assertExactJson(['data' => [
                'id' => 1,
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'email' => 'jane.doe@example.com',
            ]]);
    }

    public function test_show_returns_404_for_missing_employee(): void
    {
        $this->getJson('/api/employees/999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Employee with ID 999 not found.']);
    }

    /**
     * @dataProvider invalidIdProvider
     */
    public function test_invalid_ids_return_json_404(string $id): void
    {
        $this->get("/api/employees/{$id}")
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure(['message']);

        $this->delete("/api/employees/{$id}")->assertNotFound();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidIdProvider(): array
    {
        return [
            'non-numeric' => ['abc'],
            'zero' => ['0'],
            'leading zeros' => ['001'],
            'negative' => ['-1'],
            'too large for an integer' => ['99999999999999999999'],
        ];
    }

    public function test_store_creates_an_employee(): void
    {
        $response = $this->postJson('/api/employees', self::VALID_PAYLOAD)
            ->assertCreated()
            ->assertJsonPath('data.id', 4)
            ->assertJsonPath('data.firstName', 'Alex')
            ->assertJsonPath('data.lastName', 'Turner')
            ->assertJsonPath('data.email', 'alex.turner@example.com');

        $response->assertHeader('Location', url('/api/employees/4'));

        $this->getJson('/api/employees/4')->assertOk()->assertJsonPath('data.email', 'alex.turner@example.com');
        $this->getJson('/api/employees')->assertJsonCount(4, 'data');
    }

    public function test_store_ignores_unknown_fields(): void
    {
        $this->postJson('/api/employees', self::VALID_PAYLOAD + ['id' => 100, 'salary' => 1])
            ->assertCreated()
            ->assertJsonPath('data.id', 4)
            ->assertJsonMissingPath('data.salary');
    }

    public function test_store_requires_all_fields(): void
    {
        $this->postJson('/api/employees', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['firstName', 'lastName', 'email'])
            ->assertJsonPath('errors.firstName.0', 'The first name field is required.');
    }

    public function test_store_rejects_invalid_email(): void
    {
        $this->postJson('/api/employees', ['email' => 'not-an-email'] + self::VALID_PAYLOAD)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonMissingValidationErrors(['firstName', 'lastName']);
    }

    public function test_store_rejects_names_that_are_too_long_or_not_strings(): void
    {
        $this->postJson('/api/employees', [
            'firstName' => str_repeat('a', 101),
            'lastName' => ['not', 'a', 'string'],
        ] + self::VALID_PAYLOAD)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['firstName', 'lastName']);
    }

    public function test_store_rejects_malformed_json_with_400(): void
    {
        $this->call('POST', '/api/employees', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], '{"firstName": "Alex",')
            ->assertStatus(400)
            ->assertJsonPath('message', 'The request body is not valid JSON.');
    }

    public function test_store_rejects_duplicate_email_case_insensitively(): void
    {
        $this->postJson('/api/employees', ['email' => 'JANE.DOE@example.com'] + self::VALID_PAYLOAD)
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'An employee with this email address already exists.');

        $this->getJson('/api/employees')->assertJsonCount(3, 'data');
    }

    public function test_destroy_deletes_an_employee(): void
    {
        $this->deleteJson('/api/employees/1')
            ->assertNoContent();

        $this->getJson('/api/employees/1')->assertNotFound();
        $this->getJson('/api/employees')
            ->assertJsonCount(2, 'data')
            ->assertJsonMissing(['email' => 'jane.doe@example.com']);
    }

    public function test_destroy_returns_404_for_missing_employee(): void
    {
        $this->deleteJson('/api/employees/999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Employee with ID 999 not found.']);
    }

    public function test_ids_are_not_reused_after_deletion(): void
    {
        $this->deleteJson('/api/employees/3')->assertNoContent();

        $this->postJson('/api/employees', self::VALID_PAYLOAD)
            ->assertCreated()
            ->assertJsonPath('data.id', 4);
    }

    public function test_unsupported_method_returns_405(): void
    {
        $this->putJson('/api/employees/1', self::VALID_PAYLOAD)
            ->assertStatus(405)
            ->assertJsonStructure(['message']);
    }
}

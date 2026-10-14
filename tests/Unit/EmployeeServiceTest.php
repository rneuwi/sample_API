<?php

namespace Tests\Unit;

use App\Exceptions\EmployeeNotFoundException;
use App\Repositories\InMemoryEmployeeRepository;
use App\Services\EmployeeService;
use PHPUnit\Framework\TestCase;

class EmployeeServiceTest extends TestCase
{
    private EmployeeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new EmployeeService(new InMemoryEmployeeRepository);
    }

    public function test_get_returns_the_employee(): void
    {
        $this->assertSame('Jane', $this->service->get(1)->firstName);
    }

    public function test_get_throws_when_employee_is_missing(): void
    {
        $this->expectException(EmployeeNotFoundException::class);
        $this->expectExceptionMessage('Employee with ID 999 not found.');

        $this->service->get(999);
    }

    public function test_create_and_list(): void
    {
        $employee = $this->service->create([
            'firstName' => 'Alex',
            'lastName' => 'Turner',
            'email' => 'alex.turner@example.com',
        ]);

        $this->assertSame(4, $employee->id);
        $this->assertCount(4, $this->service->list());
    }

    public function test_delete_throws_when_employee_is_missing(): void
    {
        $this->expectException(EmployeeNotFoundException::class);

        $this->service->delete(999);
    }

    public function test_delete_removes_the_employee(): void
    {
        $this->service->delete(1);

        $this->expectException(EmployeeNotFoundException::class);
        $this->service->get(1);
    }
}

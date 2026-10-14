<?php

namespace Tests\Unit;

use App\Entities\Employee;
use App\Repositories\InMemoryEmployeeRepository;
use PHPUnit\Framework\TestCase;

class InMemoryEmployeeRepositoryTest extends TestCase
{
    public function test_it_is_seeded_with_sample_employees_by_default(): void
    {
        $repository = new InMemoryEmployeeRepository;

        $this->assertCount(3, $repository->all());
        $this->assertContainsOnlyInstancesOf(Employee::class, $repository->all());
    }

    public function test_it_can_start_from_a_custom_seed(): void
    {
        $repository = new InMemoryEmployeeRepository([]);

        $this->assertSame([], $repository->all());
    }

    public function test_create_assigns_incrementing_ids(): void
    {
        $repository = new InMemoryEmployeeRepository([]);

        $first = $repository->create(['firstName' => 'A', 'lastName' => 'One', 'email' => 'a@example.com']);
        $second = $repository->create(['firstName' => 'B', 'lastName' => 'Two', 'email' => 'b@example.com']);

        $this->assertSame(1, $first->id);
        $this->assertSame(2, $second->id);
        $this->assertEquals($second, $repository->find(2));
    }

    public function test_find_returns_null_for_unknown_id(): void
    {
        $this->assertNull((new InMemoryEmployeeRepository)->find(999));
    }

    public function test_find_by_email_is_case_insensitive(): void
    {
        $repository = new InMemoryEmployeeRepository;

        $this->assertSame(1, $repository->findByEmail('Jane.Doe@Example.com')?->id);
        $this->assertNull($repository->findByEmail('nobody@example.com'));
    }

    public function test_delete_removes_the_employee(): void
    {
        $repository = new InMemoryEmployeeRepository;

        $this->assertTrue($repository->delete(1));
        $this->assertNull($repository->find(1));
        $this->assertCount(2, $repository->all());
        $this->assertFalse($repository->delete(1));
    }
}

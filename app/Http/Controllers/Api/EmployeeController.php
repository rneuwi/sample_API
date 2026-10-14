<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeIdRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $employees) {}

    /**
     * GET /api/employees
     */
    public function index(): AnonymousResourceCollection
    {
        return EmployeeResource::collection($this->employees->list());
    }

    /**
     * GET /api/employees/{employee}
     */
    public function show(EmployeeIdRequest $request, int $employee): EmployeeResource
    {
        return new EmployeeResource($this->employees->get($employee));
    }

    /**
     * POST /api/employees
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employees->create($request->validated());

        return (new EmployeeResource($employee))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('employees.show', $employee->id));
    }

    /**
     * DELETE /api/employees/{employee}
     */
    public function destroy(EmployeeIdRequest $request, int $employee): Response
    {
        $this->employees->delete($employee);

        return response()->noContent();
    }

    // Future: an update(UpdateEmployeeRequest $request, int $employee) action
    // would go here, backed by EmployeeService::update() and returning the
    // updated EmployeeResource with a 200 status.
}

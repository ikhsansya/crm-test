<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use Illuminate\Support\Facades\DB;
use App\Services\UserService;
use App\Models\Company;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateCompanyRequest;

class CompanyController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $columns = ['name'];
        $limit = $request->query('limit', 2);

        $query = Company::query()
            ->search($request, $columns)
            ->filterSort($request)
            ->paginate($limit)
            ->appends([
                'limit' => $limit,
            ]);
    
        return $this->successResponse($query);
    }

    public function store(StoreCompanyRequest $request, UserService $userService)
    {
        DB::beginTransaction(); // Start the transaction

        try {
            // Create the company
            $company = Company::create($request->all());

            // Create a manager user for the company
            $userData = [
                'name' => $request->manager_name,
                'email' => $request->manager_email,
                'password' => $request->manager_password ?? $request->manager_email, // Set password same as email if not defined
                'role' => 'manager',
                'company_id' => $company->id
            ];

            $user = $userService->createUser($userData);

            if (!$user) {
                throw new \Exception('Failed to create manager user');
            }

            DB::commit();

            $company['manager'] = $userData;

            return $this->createdResponse($company, 'Company and manager user created successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->internalErrorResponse([], 'Failed to create company: ' . $e->getMessage());
        }
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $company->update($validatedData);

            DB::commit();

            return $this->successResponse($company, 'Company updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            // Return the error response with the exception message
            return $this->internalErrorResponse([], 'Failed to update company: ' . $e->getMessage());
        }
    }

    public function show(Company $company)
    {
        return $this->successResponse($company);
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return $this->successResponse([], 'Company deleted successfully');
    }
}

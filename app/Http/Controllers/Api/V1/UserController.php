<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Models\User;
use App\Services\UserService;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request) 
    {
        $limit = $request->query('limit', 2);
        $columns = ['name'];

        $user = auth()->user();
    
        $query = User::with('roles');

        if ($user->hasRole('manager')) {
            $query->whereHas('roles', function ($q) {
                $q->whereIn('name', ['manager', 'employee']);
            });
        } elseif ($user->hasRole('employee')) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', 'employee');
            });
        }

        $query->where('company_id', $user->company_id)
            ->search($request, $columns)
            ->filterSort($request)
            ->paginate($limit)
            ->appends([
                'limit' => $limit,
            ]);
    
        // Paginate and return the result
        $result = $query->paginate($limit)->appends([
            'limit' => $limit,
        ]);
    
        return $this->successResponse($result);
    }

    public function show(User $user) 
    {
        return $this->successResponse($user);
    }

    public function store(StoreUserRequest $request, UserService $userService)
    {
        $companyId = auth()->user()->company_id;

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'password' => $request->password,
            'role' => 'employee', //hardcoded because only manager can create an employee
            'company_id' => $companyId
        ];

        $user = $userService->createUser($userData);

        if ($user) {
            return $this->createdResponse($user, 'User created successfully');
        }

        return $this->internalErrorResponse([], 'Failed to create user');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        DB::beginTransaction();

        try {
            $authenticatedUser = auth()->user();

            if (
                $user->company_id !== $authenticatedUser->company_id &&
                $user->id !== $authenticatedUser->id
            ) {
                return $this->forbiddenResponse('You can only update employees in your company or your own data.');
            }

            $validatedData = $request->validated();

            $user->update($validatedData);

            DB::commit();

            return $this->successResponse($user, 'User updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->internalErrorResponse([], 'Failed to update user: ' . $e->getMessage());
        }
    }


    public function destroy(User $user) 
    {
        $user->delete();
        return $this->successResponse([], 'User deleted successfylly');
    }
}

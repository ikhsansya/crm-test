<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    /**
     * Create a new user and assign a role.
     *
     * @param array $data
     * @return User|null
     */
    public function createUser(array $data): ?User
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'address' => $data['address'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'password' => Hash::make($data['password']),
                'company_id' => $data['company_id']
            ]);

            $user->assignRole($data['role']);

            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to create user: ' . $e->getMessage());
            return null;
        }
    }
}

<?php
namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class CompanySeeder extends Seeder
{
    public function run()
    {
        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create company
            $company = Company::create([
                'name' => 'Example Company',
                'email' => 'example@company.com',
                'phone_number' => '123456789',
            ]);

            // Create roles using Spatie Permission with guard_name
            $superadminRole = Role::create([
                'name' => 'superadmin',
                'guard_name' => 'api',
            ]);
            $managerRole = Role::create([
                'name' => 'manager',
                'guard_name' => 'api',
            ]);
            $employeeRole = Role::create([
                'name' => 'employee',
                'guard_name' => 'api',
            ]);

            // Create superadmin user
            $superAdmin = User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('superadminpassword'),
            ]);
            $superAdmin->assignRole($superadminRole);

            // Create manager user for the company
            $manager = User::create([
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '123456789',
                'address' => 'Manager Address',
                'company_id' => $company->id,
            ]);
            $manager->assignRole($managerRole);

            // Create employee users
            $employee1 = User::create([
                'name' => 'Employee One',
                'email' => 'employee1@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '987654321',
                'address' => 'Employee One Address',
                'company_id' => $company->id,
            ]);
            $employee1->assignRole($employeeRole);

            $employee2 = User::create([
                'name' => 'Employee Two',
                'email' => 'employee2@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '987654322',
                'address' => 'Employee Two Address',
                'company_id' => $company->id,
            ]);
            $employee2->assignRole($employeeRole);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            
            throw $e;
        }
    }
}

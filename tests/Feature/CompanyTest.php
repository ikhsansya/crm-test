<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Company;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed database with necessary roles and permissions
        $this->seed();
    }

    /**
     * Helper method to authenticate a user and return headers.
     */
    private function getAuthHeaders(User $user): array
    {
        $token = JWTAuth::fromUser($user);
        return ['Authorization' => "Bearer $token"];
    }

    public function test_superadmin_can_create_company()
    {
        // Arrange
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $headers = $this->getAuthHeaders($superadmin);

        $requestData = [
            'name' => 'Test Company',
            'email' => 'company-testing@example.com',
            'phone_number' => '081111111112',
            'manager_name' => 'Test Manager',
            'manager_email' => 'manager-testing@example.com',
            'manager_password' => 'password',
        ];

        // Act
        $response = $this->withHeaders($headers)
                        ->postJson(route('company.store'), $requestData);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('companies', ['name' => 'Test Company']);
    }

    public function test_non_superadmin_cannot_create_company()
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('employee');

        $headers = $this->getAuthHeaders($user);

        $requestData = [
            'name' => 'Test Company',
            'manager_name' => 'Test Manager',
            'email' => 'company-testing@example.com',
            'phone_number' => '081111111112',
            'manager_email' => 'manager-testing@example.com',
            'manager_password' => 'password',
        ];

        // Act
        $response = $this->withHeaders($headers)
                        ->postJson(route('company.store'), $requestData);

        // Assert
        $response->assertStatus(403);
        $this->assertDatabaseMissing('companies', ['name' => 'Test Company']);
    }

    public function test_company_creation_with_valid_data()
    {
        // Arrange
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $headers = $this->getAuthHeaders($superadmin);

        $requestData = [
            'name' => 'Valid Company',
            'email' => 'company-testing-valid@example.com',
            'phone_number' => '081111111113',
            'manager_name' => 'Valid Manager',
            'manager_email' => 'manager-testing-valid@example.com',
            'manager_password' => 'password',
        ];

        // Act
        $response = $this->withHeaders($headers)
                        ->postJson(route('company.store'), $requestData);

        // Assert
        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'Valid Company']);
        $this->assertDatabaseHas('companies', ['name' => 'Valid Company']);
    }

    public function test_company_creation_with_invalid_data()
    {
        // Arrange
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $headers = $this->getAuthHeaders($superadmin);

        $requestData = [
            'name' => '', // Invalid name
            'email' => 'invalid-email', // Invalid email
            'phone_number' => 'invalid-phone-number', // Invalid phone number
            'manager_name' => 'Valid Manager',
            'manager_email' => 'invalid-email', // Invalid manager email
            'manager_password' => '', // Missing password
        ];

        // Act
        $response = $this->withHeaders($headers)
                        ->postJson(route('company.store'), $requestData);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name',
            'email',
            'phone_number',
            'manager_email',
            'manager_password',
        ]);

        $this->assertDatabaseMissing('companies', ['name' => '']);
    }

    public function test_default_manager_roles_assigned_on_company_creation()
    {
        // Arrange
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');
        $headers = $this->getAuthHeaders($superadmin);

        $requestData = [
            'name' => 'Test Company',
            'email' => 'company-testing@example.com',
            'phone_number' => '081111111112',
            'manager_name' => 'Test Manager',
            'manager_email' => 'manager-testing@example.com',
            'manager_password' => 'password',
        ];

        // Act
        $response = $this->withHeaders($headers)
                        ->postJson(route('company.store'), $requestData);

        // Assert company creation was successful
        $response->assertStatus(201);
        $this->assertDatabaseHas('companies', ['name' => 'Test Company']);

        // Assert roles assigned
        $company = Company::where('name', 'Test Company')->first();
        $manager = $company->users->first();

        $this->assertTrue($manager->hasRole('manager'));
    }

}

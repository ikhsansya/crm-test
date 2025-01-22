<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function getAuthHeaders(User $user): array
    {
        $token = JWTAuth::fromUser($user);
        return ['Authorization' => "Bearer $token"];
    }

    public function test_create_employee_with_valid_data_as_manager()
    {
        // Arrange
        $manager = User::factory()->create();
        $manager->assignRole('manager');
        $headers = $this->getAuthHeaders($manager);

        $requestData = [
            'name' => 'Valid Employee',
            'email' => 'employee-test@example.com',
            'phone_number' => '08123456789',
            'password' => 'password',
            'role' => 'employee', 
        ];

        // Act
        $response = $this->withHeaders($headers)
                        ->postJson(route('user.store'), $requestData);

        // Assert
        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'Valid Employee']);
        $this->assertDatabaseHas('users', ['email' => 'employee-test@example.com']);
    }

    public function test_update_employee_information_as_manager()
    {
        // Arrange
        $manager = User::factory()->create();
        $manager->assignRole('manager');
        $headers = $this->getAuthHeaders($manager);

        $employee = User::factory()->create(); 

        $updatedData = [
            'name' => 'Updated Employee',
            'email' => 'updated-employee@example.com',
            'phone_number' => '0987654321',
        ];

        // Act
        $response = $this->withHeaders($headers)
                        ->putJson(route('user.update', $employee->id), $updatedData);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Updated Employee']);
        $this->assertDatabaseHas('users', ['email' => 'updated-employee@example.com']);
    }
    
    public function test_delete_employee_with_soft_delete_as_manager()
    {
        // Arrange
        $manager = User::factory()->create();
        $manager->assignRole('manager');
        $headers = $this->getAuthHeaders($manager);

        $employee = User::factory()->create(); // Assuming soft delete is set up

        // Act
        $response = $this->withHeaders($headers)
                        ->deleteJson(route('user.destroy', $employee->id));

        // Assert
        $response->assertStatus(200);
        $this->assertSoftDeleted('users', ['id' => $employee->id]);
    }

    public function test_view_employees_as_manager()
    {
        // Arrange
        $manager = User::factory()->create();
        $manager->assignRole('manager');
        $headers = $this->getAuthHeaders($manager);

        // Act
        $response = $this->withHeaders($headers)
                 ->getJson(route('user.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'current_page',
                'data' => [
                    '*' => ['id', 'name', 'email', 'phone_number']
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ],
            'code'
        ]);
    }

    public function test_manager_can_update_their_own_information()
    {
        // Arrange
        $manager = User::factory()->create();
        $manager->assignRole('manager');
        $headers = $this->getAuthHeaders($manager);

        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated-email@example.com',
        ];

        // Act
        $response = $this->withHeaders($headers)
                        ->putJson(route('user.update', $manager->id), $updatedData);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['name' => 'Updated Name']);
    }

    public function test_employee_can_view_details_of_fellow_employees()
    {
        // Arrange
        $employee = User::factory()->create();
        $employee->assignRole('employee');
        $headers = $this->getAuthHeaders($employee);

        $fellowEmployee = User::factory()->create();
        $fellowEmployee->assignRole('employee');

        // Act
        $response = $this->withHeaders($headers)
                        ->getJson(route('user.show', $fellowEmployee->id));

        // Assert
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $fellowEmployee->id]);
    }

}

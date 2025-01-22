<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponseTrait;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    use ApiResponseTrait;

    // Injecting JWTAuth into the constructor
    protected $jwtAuth;

    public function __construct(JWTAuth $jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
    }

    public function login(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        // Attempt to authenticate the user
        if (!$token = $this->jwtAuth->attempt($credentials)) {
            return $this->unauthorizedResponse();
        }


        // Return the token if authentication is successful
        return $this->successResponse(new UserResource($user, $token));
    }
}

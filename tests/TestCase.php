<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    /**
     * Authenticate a user using JWT for testing
     *
     * @param User $user
     * @param array $customClaims Additional claims to add to the token
     * @return string The JWT token
     */
    protected function authenticateUser(User $user, array $customClaims = []): string
    {
        return JWTAuth::fromUser($user, $customClaims);
    }

    /**
     * Act as a user with JWT authentication
     *
     * @param User $user
     * @param array $customClaims
     * @return $this
     */
    protected function actingAsUser(User $user, array $customClaims = [])
    {
        $token = $this->authenticateUser($user, $customClaims);
        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }
}

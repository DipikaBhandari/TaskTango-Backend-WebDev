<?php

namespace Controllers;

use Firebase\JWT\JWT;
use Services\userservice;

class UserController extends Controller
{
    private $service;

    public function __construct ()
    {
        parent::__construct();
        $this->service = new userservice();
    }

     function login()
    {
        $postedUser = $this->createObjectFromPostedJson("Models\\User");
        $user = $this->service->verifyUser($postedUser->email, $postedUser->password);

        if (!$user) {
            $this->respondWithError(401, "Invalid Login");
            return;
        }
        try {
            $tokenResponse = $this->generateJWT($user);
            $this->respond($tokenResponse);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getAllUsers()
    {
        try {
            $decoded = $this->checkForJwt();
            if (!$decoded) {
                $this->respondWithError(401, 'Unauthorized');
                return;
            }

            // Assuming role check or authorization here if needed

            $users = $this->service->getAllUsers();
            if ($users !== false) {
                $this->respond($users);
            } else {
                $this->respondWithError(403, 'Forbidden');
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function generateJWT($user)
    {
        $issuedAt = time();
        $accessExpire = $issuedAt + 5600; // 1 hour for access token
        $refreshExpire = $issuedAt + 1209600; // 2 weeks for refresh token

        $accessToken = JWT::encode([
            "iss" => 'localhost.com',
            "aud" => 'localhost.com',
            "iat" => $issuedAt,
            "nbf" => $issuedAt,
            "exp" => $accessExpire,
            "data" => [
                "user_id" => $user->user_id,
                "username" => $user->username,
                "email" => $user->email,
                "role" => $user->role
            ]
        ], $this->jwtSecretKey, 'HS256');

        $refreshToken = JWT::encode([
            "iss" => 'localhost.com',
            "aud" => 'localhost.com',
            "iat" => $issuedAt,
            "exp" => $refreshExpire,
            "data" => [
                "user_id" => $user->user_id
            ]
        ], $this->jwtSecretKey, 'HS256');

        return [
            "authToken" => $accessToken,
            "refreshToken" => $refreshToken,
            "expiresIn" => $accessExpire
        ];
    }
}
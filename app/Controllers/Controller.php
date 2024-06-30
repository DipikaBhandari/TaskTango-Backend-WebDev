<?php

namespace Controllers;

require __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Controller
{

    function Key()
    {
        $secret_key = bin2hex(openssl_random_pseudo_bytes(32));
        echo "Generated Secret Key: " . $secret_key;
    }

    protected $jwtSecretKey;

    function __construct()
    {
        $this->jwtSecretKey = '77ee22f5b302a27d5fe35d7e5aef7067c8abce6bd220ea3e5e68100299b0d952';
    }

    function checkforJwt()
    {
        // Check for token header
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $this->respondWithError(401, "No token provided");
            return null;
        }

        // Read JWT from header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $parts = explode(" ", $authHeader);

        // Verify if the Authorization header is properly formatted
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            $this->respondWithError(401, "Invalid token format");
            return null;
        }

        $jwt = $parts[1];

        try {
            // Decode the token
            $decoded = JWT::decode($jwt, new Key($this->jwtSecretKey, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            $this->respondWithError(401, $e->getMessage());
            return null;
        }
    }

    function respond($data)
    {
        $this->respondWithCode(200, $data);
    }

    function respondWithError($httpcode, $message)
    {
        $data = array('errorMessage' => $message);
        $this->respondWithCode($httpcode, $data);
    }

    private function respondWithCode($httpcode, $data)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpcode);
        echo json_encode($data);
    }

    function createObjectFromPostedJson($className)
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json);

        $object = new $className();
        foreach ($data as $key => $value) {
            if(is_object($value)) {
                continue;
            }
            $object->{$key} = $value;
        }
        return $object;
    }
}
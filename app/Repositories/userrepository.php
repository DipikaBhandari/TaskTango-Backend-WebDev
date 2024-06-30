<?php

namespace Repositories;
use Models\user;
use PDO;
use Services\userservice;

class userrepository extends repository
{
    public function verifyUser($email, $password)
    {
        try {
            // retrieve the user with the given email
            $stmt = $this->connection->prepare("SELECT user_id, email, password, username, role FROM User WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\user');
            $user = $stmt->fetch();

            // verify if the password matches the hash in the database
            $result = $this->verifyPassword($password, $user->password);

            if (!$result)
                return false;

            // do not pass the password hash to the caller
            $user->password = "";

            return $user;


        } catch (PDOException $e) {
            // Handle exception
        }
    }

    function verifyPassword($input, $hash)
    {
        return password_verify($input, $hash);
    }

// Check if the user is an admin
     function isAdmin($userId) {
        try {
            $stmt = $this->connection->prepare("SELECT role FROM User WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $role = $stmt->fetchColumn();
            return $role === 'admin';
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAllUsers()
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM User");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users;
        } catch (PDOException $e) {
            // Log or handle the database error appropriately
            throw new Exception("Error fetching users: " . $e->getMessage());
        }
    }

}
<?php

namespace Services;

use Repositories\userrepository;

class userservice
{
    private $repository;

    function __construct()
    {
        $this->repository = new userrepository();
    }
    public function verifyUser($email, $password)
    {
        return $this->repository->verifyUser($email, $password);
    }

    public function isAdmin($user_id)
    {
        return $this->repository->isAdmin($user_id);
    }

    public function getAllUsers()
    {
        return $this->repository->getAllUsers();
    }
}
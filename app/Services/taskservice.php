<?php

namespace Services;

use Models\Task;
use Repositories\taskrepository;

class taskservice
{
    private $repository;

    function __construct()
    {
        $this->repository = new taskrepository();
    }

    public function getPaginatedTasksByUserId($user_id, $limit, $offset)
    {
        return $this->repository->getPaginatedTasksByUserId($user_id, $limit, $offset);
    }

    public function getAllTasksByUserId($user_id)
    {
        return $this->repository->getAllTasksByUserId($user_id);
    }

    public function getAllTasks($user_id)
    {
        return $this->repository->getAllTasks($user_id);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    // Update a task
    public function update(Task $task)
    {
        return $this->repository->update($task);
    }

    public function createTask(Task $task)
    {
        return $this->repository->createTask($task);
    }

    public function getTaskOwnerId($taskId)
    {
        return $this->repository->getTaskOwnerId($taskId);
    }

    public function getAllTasksWithUsernames()
    {
        return $this->repository->getAllTasksWithUsernames();
    }

    public function updateTaskOwner($taskId, $newOwnerId)
    {
        return $this->repository->updateTaskOwner($taskId, $newOwnerId);
    }
}
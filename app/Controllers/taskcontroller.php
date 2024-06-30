<?php

namespace Controllers;

use Services\TaskService;

class taskcontroller extends Controller
{
    private $service;


    function __construct()
    {
        parent::__construct();
        $this->service = new TaskService();
    }

    function getPaginatedTasksByUserId()
    {
        try {
            $decoded = $this->checkForJwt();
            if (!$decoded) {
                return;
            }
            $userId = $decoded->data->user_id;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $tasks = $this->service->getPaginatedTasksByUserId($userId, $limit, $offset);
            $this->respond($tasks);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    function getAllTasksByUserId()
    {
        try {
            $decoded = $this->checkForJwt();
            if (!$decoded) {
                return;
            }
            $userId = $decoded->data->user_id;

            $tasks = $this->service->getAllTasksByUserId($userId);
            $this->respond($tasks);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getAllTasks() {
        try {
            $decoded = $this->checkForJwt();
            if (!$decoded) {
                $this->respondWithError(401, 'Unauthorized');
                return;
            }

            $userId = $decoded->data->user_id;

            $tasks = $this->service->getAllTasks($userId);
            if (is_array($tasks)) {
                $this->respond($tasks);
            } else {
                $this->respondWithError(403, 'Forbidden');
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getAllTasksWithUsernames()
    {
        try {
            $decoded = $this->checkForJwt();
            if (!$decoded) {
                $this->respondWithError(401, 'Unauthorized');
                return;
            }

            // Verify current user's role
            $currentUserRole = $decoded->data->role;
            if ($currentUserRole !== 'admin') {
                $this->respondWithError(403, 'Forbidden');
                return;
            }

            // Retrieve tasks with their associated owner usernames
            $tasks = $this->service->getAllTasksWithUsernames();

            if (is_array($tasks)) {
                $this->respond($tasks);
            } else {
                $this->respondWithError(403, 'Forbidden');
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    function delete($id)
    {
        try {
            $decoded = $this->checkForJwt();
            if (!$decoded) {
                return;
            }

            $deleteCount = $this->service->delete($id);
            if ($deleteCount > 0) {
                $this->respond(['message' => 'Task deleted successfully']);
            } else {
                $this->respondWithError(404, 'Task not found');
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    function update($id)
    {
        try {
            $decoded = $this->checkForJwt();
            if (!$decoded) {
                $this->respondWithError(401, 'Unauthorized');
                return;
            }

            $currentUserId = $decoded->data->user_id;
            $currentUserRole = $decoded->data->role;

            if (!$currentUserId || !$currentUserRole) {
                $this->respondWithError(403, 'Forbidden');
                return;
            }

            $taskData = $this->createObjectFromPostedJson("Models\\Task");
            $taskData->id = $id;

            // Check if the user is authorized to update the task
            $taskOwner = $this->service->getTaskOwnerId($id);
            if ($taskOwner !== $currentUserId && $currentUserRole !== 'admin') {
                $this->respondWithError(403, 'Forbidden');
                return;
            }

            $updateCount = $this->service->update($taskData);
            if ($updateCount > 0) {
                $this->respond(['message' => 'Task updated successfully']);
            } else {
                $this->respondWithError(404, 'Task not found or no changes made');
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    function createTask()
    {
        $decoded = $this->checkForJwt();
        if (!$decoded) {
            return;
        }
        try {
            $taskData = $this->createObjectFromPostedJson("Models\\task");
            $taskData->user_id = $decoded->data->user_id;
            $id = $this->service->createTask($taskData);
            if ($id) {
                $this->respond(['id' => $id]);
            } else {
                $this->respondWithError(500, 'Failed to create task');
            }

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function updateTaskOwner($taskId, $newOwnerId)
    {
        try {
            // Check JWT authentication
            $decoded = $this->checkForJwt();
            if (!$decoded) {
                $this->respondWithError(401, 'Unauthorized');
                return;
            }

            // Verify current user's role
            $currentUserRole = $decoded->data->role;
            if ($currentUserRole !== 'admin') {
                $this->respondWithError(403, 'Forbidden');
                return;
            }

            // Check if the task exists and get its current owner ID
            $currentOwnerId = $this->service->getTaskOwnerId($taskId);
            if ($currentOwnerId === false) {
                $this->respondWithError(404, 'Task not found');
                return;
            }

            // Update the task owner
            $this->service->updateTaskOwner($taskId, $newOwnerId);


        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

}
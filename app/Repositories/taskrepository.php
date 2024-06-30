<?php

namespace Repositories;

use Models\Task;
use PDO;
use Services\userservice;

class taskrepository extends repository
{


    function getPaginatedTasksByUserId($user_id, $limit, $offset) {

        $stmt = $this->connection->prepare("SELECT * FROM task WHERE user_id = :user_id 
                                                   LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':user_id', $user_id); // Binding the variable
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $model= $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $model;
    }

    function getAllTasksByUserId($user_id) {

        $stmt = $this->connection->prepare("SELECT * FROM task WHERE user_id =:user_id");
        $stmt->bindParam(':user_id', $user_id); // Binding the variable

        $stmt->execute();

        $model= $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $model;

    }

    public function getAllTasks($userId) {
        $userService = new userservice();
        if ($userService->isAdmin($userId)) {
            $stmt = $this->connection->prepare("SELECT * FROM task");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            http_response_code(403);
            return ['message' => 'Forbidden'];
        }
    }

    // Delete a task
    function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM task WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (PDOException $e) {

        }
    }

    // Update a task
    function update(Task $task)
    {
        try {
            $stmt = $this->connection->prepare("UPDATE task SET title = :title, content = :content, deadline = :deadline, category = :category, created_at = :created_at WHERE id = :id");
            $stmt->bindParam(':title', $task->title);
            $stmt->bindParam(':content', $task->content);
            $stmt->bindParam(':deadline', $task->deadline);
            $stmt->bindParam(':category', $task->category);
            $stmt->bindParam(':created_at', $task->created_at);
            $stmt->bindParam(':id', $task->id);

            $stmt->execute();

            return $stmt->rowCount();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function createTask(Task $task): bool
    {
        $stmt = $this->connection->prepare("INSERT INTO task (title, content, created_at, deadline, category, user_id) 
        VALUES (:title, :content, now(), :deadline, :category, :user_id)");

        $stmt->bindParam(':title', $task->title);
        $stmt->bindParam(':content', $task->content);
        $stmt->bindParam(':deadline', $task->deadline);
        $stmt->bindParam(':category', $task->category);
        $stmt->bindParam(':user_id', $task->user_id);

        $stmt->execute();
        return $this->connection->lastInsertId();
    }

    // Get the owner ID of a task
    function getTaskOwnerId($taskId)
    {
        try {
            $stmt = $this->connection->prepare("SELECT user_id FROM task WHERE id = :id");
            $stmt->bindParam(':id', $taskId);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }
// TaskService.php or TaskRepository.php
    public function getAllTasksWithUsernames()
    {
        try {
            // Build your SQL query to join task and user tables
            $stmt = $this->connection->prepare("
            SELECT t.*, u.username AS owner_username
            FROM task t
            JOIN User u ON t.user_id = u.user_id
        ");
            $stmt->execute();

            // Fetch all tasks with associated owner usernames
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $tasks;
        } catch (PDOException $e) {
            // Handle database errors as needed
            return false;
        }
    }


    // Update the owner (user_id) of a task
    public function updateTaskOwner($taskId, $newOwnerId)
    {
        try {
            // Update the task owner (user_id)
            $stmt = $this->connection->prepare("UPDATE task SET user_id = :user_id WHERE id = :id");
            $stmt->bindParam(':user_id', $newOwnerId);
            $stmt->bindParam(':id', $taskId);
            $stmt->execute();

            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            return false; // Handle database error as needed
        }
    }
}
<?php
require_once '../cors_config.php'; // Include CORS configuration if needed
require_once '../db_Connection/db_Connection.php'; // Include database connection

function getTasksByDay($day, $userId)
{
    global $conn;
    $sql = "SELECT * FROM tasks WHERE task_date = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return array();
    }
    $stmt->bind_param('si', $day, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = array();
    while ($row = $result->fetch_assoc()) {
        $tasks[] = array(
            'id' => $row['id'],
            'task' => $row['task'],
            'completed' => $row['completed'],
            'task_time' => $row['task_time']
        );
    }
    return $tasks;
}

function markTaskAsCompleted($taskId, $userId)
{
    global $conn;
    $sql = "UPDATE tasks SET completed = 1 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    $stmt->bind_param('ii', $taskId, $userId);
    return $stmt->execute();
}

function getTasks($userId)
{
    global $conn;
    $sql = "SELECT * FROM tasks WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return array();
    }
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = array();
    while ($row = $result->fetch_assoc()) {
        $tasks[] = array(
            'id' => $row['id'],
            'task' => $row['task'],
            'completed' => $row['completed'],
            'task_date' => $row['task_date'],
            'task_time' => $row['task_time']
        );
    }
    return $tasks;
}

function addTask($date, $task, $userId, $taskTime = null)
{
    global $conn;
    $sql = "INSERT INTO tasks (task_date, task, completed, user_id, task_time) VALUES (?, ?, 0, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    $stmt->bind_param('ssis', $date, $task, $userId, $taskTime);
    return $stmt->execute();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['day']) && isset($_GET['userId'])) {
        $tasks = getTasksByDay($_GET['day'], $_GET['userId']);
        echo json_encode($tasks);
    } elseif (isset($_GET['userId'])) {
        $tasks = getTasks($_GET['userId']);
        echo json_encode($tasks);
    } else {
        echo json_encode(array("success" => false, "message" => "Missing parameters"));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['taskId']) && isset($_POST['userId'])) {
        $result = markTaskAsCompleted($_POST['taskId'], $_POST['userId']);
        echo json_encode(['success' => $result]);
    } elseif (isset($_POST['task']) && isset($_POST['date']) && isset($_POST['userId'])) {
        $task = $_POST['task'];
        $date = $_POST['date'];
        $userId = $_POST['userId'];
        $taskTime = isset($_POST['taskTime']) ? $_POST['taskTime'] : null;
        $result = addTask($date, $task, $userId, $taskTime);
        echo json_encode(['success' => $result]);
    } else {
        echo json_encode(array("success" => false, "message" => "Missing parameters"));
    }
}
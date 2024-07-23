<?php
require_once '../cors_config.php'; // Include CORS configuration if needed
require_once '../db_Connection/db_Connection.php'; // Include database connection

function getTasksByDay($day, $userId)
{
    global $conn;
    $sql = "SELECT * FROM tasks WHERE task_date = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $day, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = array();
    while ($row = $result->fetch_assoc()) {
        $tasks[] = array(
            'id' => $row['id'],
            'task' => $row['task'],
            'completed' => $row['completed']
        );
    }
    return $tasks;
}

function markTaskAsCompleted($taskId, $userId)
{
    global $conn;
    $sql = "UPDATE tasks SET completed = 1 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $taskId, $userId);
    return $stmt->execute();
}

function getTasks($userId)
{
    global $conn;
    $sql = "SELECT * FROM tasks WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = array();
    while ($row = $result->fetch_assoc()) {
        $tasks[] = array(
            'id' => $row['id'],
            'task' => $row['task'],
            'completed' => $row['completed'],
            'task_date' => $row['task_date']
        );
    }
    return $tasks;
}

function addTask($date, $task, $userId)
{
    global $conn;
    $sql = "INSERT INTO tasks (task_date, task, completed, user_id) VALUES (?, ?, 0, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $date, $task, $userId);
    return $stmt->execute();
}

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['day']) && isset($_GET['userId'])) {
        $tasks = getTasksByDay($_GET['day'], $_GET['userId']);
        echo json_encode($tasks);
    } else {
        $tasks = getTasks($_GET['userId']);
        echo json_encode($tasks);
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
        $result = addTask($date, $task, $userId);
        echo json_encode(['success' => $result]);
    }
}

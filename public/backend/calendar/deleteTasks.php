<?php
require_once '../cors_config.php'; // Include CORS configuration if needed
require_once '../db_Connection/db_Connection.php'; // Include database connection

/**
 * Delete a task from the database.
 *
 * @param int $taskId The ID of the task to be deleted.
 * @param int $userId The ID of the user who owns the task.
 * @return bool True on success, false on failure.
 */
function deleteTask($taskId, $userId)
{
    global $conn;
    $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    $stmt->bind_param('ii', $taskId, $userId);
    return $stmt->execute();
}

header('Content-Type: application/json');

// Check if the request method is DELETE
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get the parameters from the query string
    if (isset($_GET['taskId']) && isset($_GET['userId'])) {
        $taskId = intval($_GET['taskId']);
        $userId = intval($_GET['userId']);

        $result = deleteTask($taskId, $userId);
        echo json_encode(['success' => $result]);
    } else {
        echo json_encode(array("success" => false, "message" => "Missing parameters"));
    }
} else {
    // Respond with an error if the method is not DELETE
    echo json_encode(array("success" => false, "message" => "Invalid request method"));
}
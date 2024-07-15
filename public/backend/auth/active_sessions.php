<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();
require_once '../db_Connection/db_Connection.php';

function trackActiveSession($user_id, $session_id)
{
    global $conn;
    // Prepare and execute the SQL statement to update the hashLogin only if it is null
    $stmt = $conn->prepare("INSERT IGNORE INTO active_sessions (user_id, session_id) VALUES (?, ?)");
    $stmt->bind_param('ss', $user_id, $session_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {

        return true;
    } else {

        return false; // Return false if the update failed or no rows were updated
    }
}

function clearActiveSession($user_id)
{
    global $conn;
    // Prepare and execute the SQL statement to update the hashLogin only if it is null
    $stmt = $conn->prepare("DELETE FROM active_sessions WHERE user_id = ?");
    $stmt->bind_param('s', $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {

        return true;
    } else {

        return false; // Return false if the update failed or no rows were updated
    }
}

function isActiveSession($user_id, $hashID)
{
    global $conn;
    require_once '../db_Connection/db_Connection.php';
    // Prepare and execute the SQL statement to update the hashLogin only if it is null
    $stmt = $conn->prepare("SELECT COUNT(*) FROM active_sessions WHERE user_id = ? AND session_id = ?");
    $stmt->bind_param('ss', $user_id, $session_id);
    // Execute the statement
    $stmt->execute();

    // Bind the result to a variable
    $stmt->bind_result($count);

    // Fetch the result
    $stmt->fetch();

    // Close the statement
    $stmt->close();



    // Return true if count is greater than 0, meaning the session is active
    return $count > 0;
}

function getActiveSessions($user_id)
{
    global $conn;
    require_once '../db_Connection/db_Connection.php';
    // Prepare and execute the SQL statement to update the hashLogin only if it is null
    $stmt = $conn->prepare("SELECT COUNT(*) FROM active_sessions WHERE user_id = ?");
    $stmt->bind_param('s', $user_id);
    // Execute the statement
    $stmt->execute();

    // Bind the result to a variable
    $stmt->bind_result($count);

    // Fetch the result
    $stmt->fetch();

    // Close the statement
    $stmt->close();

    if ($count > 0) {

        return true;
    } else {

        return false;
    }
}
<?php
session_start();
include('db_connection.php');

// Set the header to return JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user inputs from POST request
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Prepare SQL query to check if the user exists
    $stmt = $conn->prepare("SELECT user_id, customer_id, staff_id, role, username, password FROM user_logins WHERE username = ?");
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $customer_id, $staff_id, $role, $db_username, $db_password);
    $stmt->fetch();

    // Check if the user exists and the password is correct
    if ($stmt->num_rows > 0 && password_verify($pass, $db_password)) {

        $_SESSION['username'] = $db_username;
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        $_SESSION['customer_id'] = $customer_id;
        $_SESSION['staff_id'] = $staff_id;

        // Return success response with user data
        echo json_encode([
            'success' => true,
            'message' => "Welcome, " . $user,
            'user' => [
                'id' => $id,
                'username' => $db_username
            ]
        ]);
    } else {
        // Return error response if username or password is incorrect
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password!'
        ]);
    }

    $stmt->close();
} else {
    // Return error response if method is not POST
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}

$conn->close();
?>
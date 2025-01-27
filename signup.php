<?php
include('db_connection.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user inputs from POST request
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';

    // Prepare SQL query to check if the user already exists
    $stmt = $conn->prepare("SELECT user_id, username FROM user_logins WHERE username = ?");
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        // User does not exist, proceed to register
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

        // Start a transaction to ensure both inserts are done atomically
        $conn->begin_transaction();

        try {
            // First, insert customer details into the customer_details table
            $stmt1 = $conn->prepare("INSERT INTO customers (phone_number) VALUES (?)");
            $stmt1->bind_param('s', $phone_number);
            $stmt1->execute();

            // Get the customer ID of the inserted customer details
            $customer_id = $stmt1->insert_id;
            $stmt1->close();

            // Now, insert user login details into the user_logins table
            $stmt2 = $conn->prepare("INSERT INTO user_logins (username, password, role, customer_id) VALUES (?, ?, 'customer', ?)");
            $stmt2->bind_param('ssi', $user, $hashed_pass, $customer_id);

            if ($stmt2->execute()) {
                // Successfully registered, get the inserted user ID
                $user_id = $stmt2->insert_id;

                // Commit the transaction
                $conn->commit();

                // Return success response with user data
                echo json_encode([
                    'success' => true,
                    'message' => 'User registered successfully!',
                    'user' => [
                        'id' => $user_id,
                        'username' => $user,
                        'phone_number' => $phone_number
                    ]
                ]);
            } else {
                // Rollback the transaction in case of an error
                $conn->rollback();

                // Return error response if registration fails
                echo json_encode([
                    'success' => false,
                    'message' => 'Error: Could not register the user in the login table.'
                ]);
            }

            $stmt2->close();
        } catch (Exception $e) {
            // Rollback the transaction if any error occurs
            $conn->rollback();

            // Return error response if an exception is thrown
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    } else {
        // User already exists, return error message
        echo json_encode([
            'success' => false,
            'message' => 'User already exists!'
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
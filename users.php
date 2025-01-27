<?php
// Include database connection
include('db_connection.php');

// Set the header to return JSON
header('Content-Type: application/json');

// Handle different request methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        handlePostRequest($conn);
        break;
    case 'PUT':
        handlePutRequest($conn);
        break;
    case 'DELETE':
        handleDeleteRequest($conn);
        break;
    case 'GET':
        handleGetRequest($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        break;
}

function handlePostRequest($conn)
{
    // Get customer inputs from the POST request
    $customerId = $_POST['customer_id'] ?? null;  // Ensure you're getting the customer_id
    $firstname = $_POST['first_name'] ?? null;
    $lastname = $_POST['last_name'] ?? null;
    $phonenumber = $_POST['phone_number'] ?? null;
    $address = $_POST['address'] ?? null;
    $username = $_POST['username'] ?? null;
    $preferred_contact_method = $_POST['preferred_contact_method'] ?? null;
    $password = $_POST['password'] ?? null;

    // Check if the customerId is provided and valid
    if ($customerId) {
        // Prepare SQL query to check if the customer exists
        $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE customer_id = ?");
        $stmt->bind_param('i', $customerId); // Assuming customer_id is an integer
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Customer exists, proceed with updating the details
            if ($firstname && $lastname && $phonenumber && $address) {
                // Prepare SQL query to update the customer's profile
                $stmt1 = $conn->prepare("UPDATE customers SET first_name = ?, last_name = ?, phone_number = ?, address = ?, preferred_contact_method = ? WHERE customer_id = ?");
                $stmt1->bind_param('ssssi', $firstname, $lastname, $phonenumber, $address, $preferred_contact_method, $customerId);

                if ($stmt1->execute()) {
                    $stmt2 = $conn->prepare("SELECT customer_id, first_name, last_name, phone_number, preferred_contact_method address FROM customers WHERE customer_id = ?");
                    $stmt2->bind_param('i', $customerId);
                    if ($stmt2->execute()) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'New customer added and login credentials created successfully.',
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Error inserting login credentials.',
                        ]);
                    }

                    $stmt2->close();
                } else {
                    // Return error message if update fails
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error: Could not update the profile.'
                    ]);
                }

                $stmt1->close();
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No fields to update.'
                ]);
            }
        } else {
            // Return error message if customer is not found
            echo json_encode([
                'success' => false,
                'message' => 'Customer not found!'
            ]);
        }

        $stmt->close();
    } else {
        // Insert new staff member into the `staff` table
        $stmt1 = $conn->prepare("INSERT INTO customers (first_name, last_name, phone_number, address, preferred_contact_method) VALUES (?, ?, ?, ?, ?)");
        $stmt1->bind_param('ssss', $firstname, $lastname, $phonenumber, $address, $preferred_contact_method);

        if ($stmt1->execute()) {
            $customerId = $stmt1->insert_id;

            // Insert login credentials into the `user_logins` table
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("INSERT INTO user_logins (customer_id, username, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt2->bind_param('iss', $customerId, $username, $hashedPassword);

            if ($stmt2->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'New customer added and login credentials created successfully.',
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error inserting login credentials.',
                ]);
            }

            $stmt2->close();
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error inserting new customer member.',
            ]);
        }

        $stmt1->close();
    }
}

function handlePutRequest($conn)
{
    // Decode JSON input for PUT requests
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid or missing input data']);
        return;
    }

    $customer_id = $data['customer_id'] ?? null;
    $username = $data['username'] ?? null;
    $firstName = $data['first_name'] ?? null;
    $lastName = $data['last_name'] ?? null;
    $phoneNumber = $data['phone_number'] ?? null;
    $address = $data['address'] ?? null;
    $preferred_contact_method = $data['preferred_contact_method'] ?? null;

    // Check if all required fields are provided
    if ($firstName && $lastName && $phoneNumber && $address && $username && $customer_id && $preferred_contact_method) {
        // Update the customer record
        $sql = "UPDATE customers 
SET first_name = ?, last_name = ?, phone_number = ?, address = ?, preferred_contact_method = ? 
WHERE customer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssi', $firstName, $lastName, $phoneNumber, $address, $preferred_contact_method, $customer_id);

        if ($stmt->execute()) {
            // After updating the customer record, update the username in the customer_logins table
            $sql_login = "UPDATE user_logins SET username = ? WHERE customer_id = ?";
            $stmt2 = $conn->prepare($sql_login);
            $stmt2->bind_param('si', $username, $customer_id);

            if ($stmt2->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Customer and login credentials updated successfully.',
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error updating login credentials.',
                ]);
            }

            $stmt2->close();
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error updating customer member.',
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide all required fields to update customer.',
        ]);
    }
}

function handleDeleteRequest($conn)
{
    // Parse input data for DELETE request
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? null;

    if ($id) {
        // SQL query to delete the customer member
        $sql = "DELETE FROM customers WHERE customer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Customer member deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting customer member.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Customer ID is required to delete a customer member.']);
    }
}

function handleGetRequest($conn)
{
    // Get customer ID from the request
    $id = $_GET['customer_id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
        return;
    }

    // SQL query to get customer details
    $sql = "
    SELECT 
        customers.customer_id, 
        customers.first_name, 
        customers.last_name, 
        customers.phone_number, 
        customers.address, 
        customers.preferred_contact_method, 
        customers.created_at AS customer_created_at, 
        customers.updated_at AS customer_updated_at, 
        user_logins.username, 
        user_logins.password, 
        user_logins.role AS login_role, 
        user_logins.created_at AS login_created_at, 
        user_logins.updated_at AS login_updated_at 
    FROM 
        customers 
    JOIN 
        user_logins 
    ON 
        customers.customer_id = user_logins.customer_id
    WHERE 
        customers.customer_id = ?;
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => $customer
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Customer not found'
        ]);
    }

    $stmt->close();
}
?>
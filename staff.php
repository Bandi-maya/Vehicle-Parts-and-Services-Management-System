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
    // Get input data
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;
    $firstName = $_POST['first_name'] ?? null;
    $lastName = $_POST['last_name'] ?? null;
    $email = $_POST['email'] ?? null;
    $phoneNumber = $_POST['phone_number'] ?? null;
    $address = $_POST['address'] ?? null;
    $hireDate = $_POST['hire_date'] ?? null;
    $status = $_POST['status'] ?? null;
    $role = $_POST['role'] ?? null;

    // Validate required fields
    if ($firstName && $lastName && $email && $phoneNumber && $address && $hireDate && $status && $role && $username && $password) {
        // Insert new staff member into the `staff` table
        $stmt1 = $conn->prepare("INSERT INTO staff (first_name, last_name, email, phone_number, address, hire_date, status, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param('ssssssss', $firstName, $lastName, $email, $phoneNumber, $address, $hireDate, $status, $role);

        if ($stmt1->execute()) {
            $staffId = $stmt1->insert_id;  // Get the newly inserted staff_id

            // Insert login credentials into the `user_logins` table
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("INSERT INTO user_logins (staff_id, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param('isss', $staffId, $username, $hashedPassword, 'staff');

            if ($stmt2->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'New staff added and login credentials created successfully.',
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
                'message' => 'Error inserting new staff member.',
            ]);
        }

        $stmt1->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide all required fields to add a new staff member.',
        ]);
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

    $staff_id = $data['staff_id'] ?? null;
    $username = $data['username'] ?? null;
    $firstName = $data['first_name'] ?? null;
    $lastName = $data['last_name'] ?? null;
    $email = $data['email'] ?? null;
    $phoneNumber = $data['phone_number'] ?? null;
    $address = $data['address'] ?? null;
    $hireDate = $data['hire_date'] ?? null;
    $status = $data['status'] ?? null;
    $role = $data['role'] ?? null;

    // Check if all required fields are provided
    if ($firstName && $lastName && $email && $phoneNumber && $address && $hireDate && $status && $role && $username && $staff_id) {
        // Update the staff record
        $sql = "UPDATE staff 
SET first_name = ?, last_name = ?, email = ?, phone_number = ?, address = ?, hire_date = ?, status = ?, role = ? 
WHERE staff_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssssi', $firstName, $lastName, $email, $phoneNumber, $address, $hireDate, $status, $role, $staff_id);

        if ($stmt->execute()) {
            // After updating the staff record, update the username in the user_logins table
            $sql_login = "UPDATE user_logins SET username = ? WHERE staff_id = ?";
            $stmt2 = $conn->prepare($sql_login);
            $stmt2->bind_param('si', $username, $staff_id);

            if ($stmt2->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Staff and login credentials updated successfully.',
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
                'message' => 'Error updating staff member.',
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide all required fields to update staff member.',
        ]);
    }
}

function handleDeleteRequest($conn)
{
    // Parse input data for DELETE request
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? null;

    if ($id) {
        // SQL query to delete the staff member
        $sql = "DELETE FROM staff WHERE staff_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Staff member deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting staff member.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Staff ID is required to delete a staff member.']);
    }
}

function handleGetRequest($conn)
{
    // Get staff ID from the request
    $id = $_GET['staff_id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Staff ID is required']);
        return;
    }

    // SQL query to get staff details
    $sql = "
    SELECT 
        staff.staff_id, 
        staff.first_name, 
        staff.last_name, 
        staff.email, 
        staff.phone_number, 
        staff.role, 
        staff.address, 
        staff.hire_date, 
        staff.status, 
        staff.created_at AS staff_created_at, 
        staff.updated_at AS staff_updated_at, 
        user_logins.username, 
        user_logins.password, 
        user_logins.role AS login_role, 
        user_logins.created_at AS login_created_at, 
        user_logins.updated_at AS login_updated_at 
    FROM 
        staff 
    JOIN 
        user_logins 
    ON 
        staff.staff_id = user_logins.staff_id
    WHERE 
        staff.staff_id = ?;
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => $staff
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Staff not found'
        ]);
    }

    $stmt->close();
}
?>
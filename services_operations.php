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
    $service_name = $_POST['service_name'] ?? null;
    $service_type = $_POST['service_type'] ?? null;
    $description = $_POST['description'] ?? null;
    $cost = $_POST['cost'] ?? null;
    $labor_time = $_POST['labor_time'] ?? null;
    $parts_needed = $_POST['parts_needed'] ?? null;
    $service_status = $_POST['service_status'] ?? null;
    if ($service_name && $service_type && $description && $cost && $labor_time && $parts_needed && $service_status) {


        $query = "
INSERT INTO services (service_name, service_type, description, cost, labor_time, parts_needed, service_status)
VALUES (?, ?, ?, ?, ?, ?, ?)
";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            'sssssss',
            $service_name,
            $service_type,
            $description,
            $cost,
            $labor_time,
            $parts_needed,
            $service_status
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Service added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding service']);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide all required fields to add a new vehicle.',
        ]);
    }
}

function handlePutRequest($conn)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $service_id = $data['service_id'] ?? null;
    $service_name = $data['service_name'] ?? null;
    $service_type = $data['service_type'] ?? null;
    $description = $data['description'] ?? null;
    $cost = $data['cost'] ?? null;
    $labor_time = $data['labor_time'] ?? null;
    $parts_needed = $data['parts_needed'] ?? null;
    $service_status = $data['service_status'] ?? null;

    if ($service_id && $service_name && $service_type && $description && $cost && $labor_time && $parts_needed && $service_status) {
        $query = "
    UPDATE services
    SET
    service_name = ?,
    service_type = ?,
    description = ?,
    cost = ?,
    labor_time = ?,
    parts_needed = ?,
    service_status = ?,
    updated_at = NOW()
    WHERE
    service_id = ?
    ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            'sssssssi',
            $service_name,
            $service_type,
            $description,
            $cost,
            $labor_time,
            $parts_needed,
            $service_status,
            $service_id
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Service updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating service']);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide all required fields to update vehicle.',
        ]);
    }
}

function handleDeleteRequest($conn)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $service_id = $data['service_id'] ?? null;

    if ($service_id) {

        $query = "
        DELETE FROM services WHERE service_id = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $service_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Service deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting service']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Service ID is required to delete a Service.']);
    }
}

function handleGetRequest($conn)
{
    $service_id = $_GET['service_id'] ?? null;

    if (!$service_id) {
        echo json_encode(['success' => false, 'message' => 'Service ID is required']);
        return;
    }

    $sql = "SELECT service_id,service_name,service_type,description,cost,labor_time,parts_needed,service_status,created_at,updated_at FROM services WHERE service_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $service_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $service = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => $service
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Service not found'
        ]);
    }

    $stmt->close();
}

// Close the connection
$conn->close();
?>
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
    // Get input data for vehicle service
    $vehicle_id = $_POST['vehicle_id'] ?? null;
    $service_id = $_POST['service_id'] ?? null;
    $service_cost = $_POST['service_cost'] ?? null;
    $labor_time = $_POST['labor_time'] ?? null;
    $parts_needed = $_POST['parts_needed'] ?? null;
    $service_date = $_POST['service_date'];

    echo $vehicle_id . $service_id . $service_cost;
    // Validate required fields
    if ($vehicle_id && $service_id && $service_cost) {
        $stmt = $conn->prepare("INSERT INTO vehicle_services (vehicle_id, service_id, service_cost, labor_time, parts_needed, service_date) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iisdss', $vehicle_id, $service_id, $service_cost, $labor_time, $parts_needed, $service_date);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'New vehicle service added successfully.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error inserting new vehicle service.',
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide all required fields to add a new service.',
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

    $vehicle_id = $data['vehicle_id'] ?? null;
    $service_id = $data['service_id'] ?? null;
    $service_cost = $data['service_cost'] ?? null;
    $labor_time = $data['labor_time'] ?? null;
    $vehicle_service_id = $data['id'] ?? null;
    $parts_needed = $data['parts_needed'] ?? null;
    $service_date = $data['service_date'] ?? null;

    // Check if all required fields are provided
    if ($vehicle_service_id) {
        // Update the vehicle service record
        $sql = "UPDATE vehicle_services 
                SET service_cost = ?, labor_time = ?, parts_needed = ?, vehicle_id = ?, service_id = ?, service_date = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('dssiisi', $service_cost, $labor_time, $parts_needed, $vehicle_id, $service_id, $service_date, $vehicle_service_id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Vehicle service record updated successfully.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error updating service record.',
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide all required fields to update the service.',
        ]);
    }
}

function handleDeleteRequest($conn)
{
    // Parse input data for DELETE request
    $data = json_decode(file_get_contents("php://input"), true);
    $vehicle_service_id = $data['id'] ?? null;

    if ($vehicle_service_id) {
        // SQL query to delete the service record for a specific vehicle
        $sql = "DELETE FROM vehicle_services WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $vehicle_service_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Service record deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting service record.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Vehicle Service ID is required to delete a service.']);
    }
}

function handleGetRequest($conn)
{
    // Get vehicle_id and service_id from the request
    $vehicle_service_id = $_GET['id'] ?? null;


    if (!$vehicle_service_id) {
        echo json_encode(['success' => false, 'message' => 'Vehicle Service ID is required']);
        return;
    }

    // SQL query to get service details for a specific vehicle
    $sql = "SELECT vehicle_services.service_id, vehicle_services.vehicle_id, services.service_name, services.service_type, 
            vehicle_services.service_cost, vehicle_services.labor_time, vehicle_services.parts_needed, vehicle_services.service_date, 
            vehicle_services.created_at, vehicle_services.updated_at 
            FROM vehicle_services 
            JOIN services ON vehicle_services.service_id = services.service_id 
            JOIN vehicles ON vehicle_services.vehicle_id = vehicles.vehicle_id 
            WHERE vehicle_services.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $vehicle_service_id);
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
            'message' => 'Service not found for the vehicle'
        ]);
    }

    $stmt->close();
}
?>
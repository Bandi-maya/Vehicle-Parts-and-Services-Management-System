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
    $customer_id = $_POST['customer_id'] ?? null;
    $make = $_POST['make'] ?? null;
    $model = $_POST['model'] ?? null;
    $year = $_POST['year'] ?? null;
    $vin = $_POST['vin'] ?? null;
    $service_history = $_POST['service_history'] ?? null;

    // Validate required fields
    if ($customer_id && $make && $model && $year && $vin && $service_history) {
        // Insert new vehicle into the `vehicles` table
        $stmt = $conn->prepare("INSERT INTO vehicles (customer_id, make, model, year, vin, service_history) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isssss', $customer_id, $make, $model, $year, $vin, $service_history);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'New vehicle added successfully.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error inserting new vehicle.',
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide all required fields to add a new vehicle.',
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
    $customer_id = $data['customer_id'] ?? null;
    $make = $data['make'] ?? null;
    $model = $data['model'] ?? null;
    $year = $data['year'] ?? null;
    $vin = $data['vin'] ?? null;
    $service_history = $data['service_history'] ?? null;
    $updated_at = date('Y-m-d H:i:s');

    // Check if all required fields are provided
    if ($vehicle_id && $customer_id && $make && $model && $year && $vin && $service_history) {
        // Update the vehicle record
        $sql = "UPDATE vehicles 
                SET customer_id = ?, make = ?, model = ?, year = ?, vin = ?, service_history = ?, updated_at = ? 
                WHERE vehicle_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('issssssi', $customer_id, $make, $model, $year, $vin, $service_history, $updated_at, $vehicle_id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Vehicle record updated successfully.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error updating vehicle record.',
            ]);
        }

        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide all required fields to update vehicle.',
        ]);
    }
}

function handleDeleteRequest($conn)
{
    // Parse input data for DELETE request
    $data = json_decode(file_get_contents("php://input"), true);
    $vehicle_id = $data['vehicle_id'] ?? null;

    if ($vehicle_id) {
        // SQL query to delete the vehicle record
        $sql = "DELETE FROM vehicles WHERE vehicle_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $vehicle_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Vehicle record deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting vehicle record.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Vehicle ID is required to delete a vehicle.']);
    }
}

function handleGetRequest($conn)
{
    // Get vehicle ID from the request
    $vehicle_id = $_GET['vehicle_id'] ?? null;
    if (!$vehicle_id) {
        echo json_encode(['success' => false, 'message' => 'Vehicle ID is required']);
        return;
    }

    // SQL query to get vehicle details
    $sql = "SELECT vehicle_id, customer_id, make, model, year, vin, service_history, created_at, updated_at FROM vehicles WHERE vehicle_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $vehicle_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $vehicle = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => $vehicle
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Vehicle not found'
        ]);
    }

    $stmt->close();
}
?>
<?php
include('db_connection.php');


if ($_SESSION['role'] === 'customer') {
    $customer_id = $_SESSION['customer_id'];

    $query = "
    SELECT 
        vehicles.vehicle_id, 
        vehicles.customer_id, 
        vehicles.make, 
        vehicles.model, 
        vehicles.year, 
        vehicles.vin, 
        vehicles.service_history, 
        vehicles.created_at, 
        vehicles.updated_at,
        customers.first_name,
        customers.last_name
    FROM 
        vehicles
    JOIN 
        customers ON vehicles.customer_id = customers.customer_id
    WHERE customers.customer_id = $customer_id
";
} else {
    $query = "
    SELECT 
        vehicles.vehicle_id, 
        vehicles.customer_id, 
        vehicles.make, 
        vehicles.model, 
        vehicles.year, 
        vehicles.vin, 
        vehicles.service_history, 
        vehicles.created_at, 
        vehicles.updated_at,
        customers.first_name,
        customers.last_name
    FROM 
        vehicles
    JOIN 
        customers ON vehicles.customer_id = customers.customer_id
";
}
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $vehicles = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $vehicles = [];
}

$conn->close();
?>

<h2>Vehicle List</h2>

<?php

if ($_SESSION['role'] !== 'customer') {
    echo '<button onclick="openAddModal()">Add New Vehicle</button>';
}
?>

<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px; border-collapse: collapse;">
    <thead>
        <tr>
            <th>Vehicle ID</th>
            <th>Customer</th>
            <th>Make</th>
            <th>Model</th>
            <th>Year</th>
            <th>VIN</th>
            <th>Service History</th>
            <th>Joined Date</th>
            <?php
            if ($_SESSION['role'] !== 'customer') {
                echo '<th>Actions</th>';
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($vehicles)): ?>
            <?php foreach ($vehicles as $vehicle): ?>
                <tr>
                    <td><?php echo $vehicle['vehicle_id']; ?></td>
                    <td><?php echo $vehicle['first_name'] . ' ' . $vehicle['last_name']; ?></td>
                    <td><?php echo $vehicle['make']; ?></td>
                    <td><?php echo $vehicle['model']; ?></td>
                    <td><?php echo $vehicle['year']; ?></td>
                    <td><?php echo $vehicle['vin']; ?></td>
                    <td><?php echo $vehicle['service_history']; ?></td>
                    <td><?php echo $vehicle['created_at']; ?></td>
                    <?php
                    if ($_SESSION['role'] !== 'customer') {
                        echo '<td>
            <button
                style="text-decoration: none; color: white; background-color: blue; padding: 5px 10px; border-radius: 5px;"
                onclick="openEditModal(' . $vehicle['vehicle_id'] . ')">Edit</button>
            <button
                style="text-decoration: none; color: white; background-color: red; padding: 5px 10px; border-radius: 5px;"
                onclick="deleteVehicle(' . $vehicle['vehicle_id'] . ')">Delete
            </button>
        </td>';
                    }
                    ?>

                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">No vehicles found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div id="editModal" style="display: none;">
    <div
        style="background-color: rgba(0, 0, 0, 0.5); width: 100%; height: 100%; position: fixed; top: 0; left: 0; display: flex; justify-content: center; align-items: center;">
        <div style="background-color: white; padding: 20px; width: 400px; border-radius: 5px;">
            <h3 id="modalTitle">Add New Vehicle</h3>
            <form id="editForm" method="POST">
                <input type="hidden" name="vehicle_id" id="vehicle_id">

                <label for="customer_id">Customer ID:</label>
                <input type="text" name="customer_id" id="customer_id" required><br><br>

                <label for="make">Make:</label>
                <input type="text" name="make" id="make" required><br><br>

                <label for="model">Model:</label>
                <input type="text" name="model" id="model" required><br><br>

                <label for="year">Year:</label>
                <input type="text" name="year" id="year" required><br><br>

                <label for="vin">VIN:</label>
                <input type="text" name="vin" id="vin" required><br><br>

                <label for="service_history">Service History:</label>
                <textarea name="service_history" id="service_history" required></textarea><br><br>

                <button type="submit">Save</button>
                <button type="button" onclick="closeEditModal()">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('modalTitle').textContent = 'Add New Vehicle';
        document.getElementById('editForm').reset();
        document.getElementById('vehicle_id').value = '';
        document.getElementById('customer_id').value = '';
    }

    function openEditModal(vehicle_id) {
        fetch('vehicle_operations.php?vehicle_id=' + vehicle_id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('vehicle_id').value = data.data.vehicle_id;
                    document.getElementById('customer_id').value = data.data.customer_id;
                    document.getElementById('make').value = data.data.make;
                    document.getElementById('model').value = data.data.model;
                    document.getElementById('year').value = data.data.year;
                    document.getElementById('vin').value = data.data.vin;
                    document.getElementById('service_history').value = data.data.service_history;

                    document.getElementById('modalTitle').textContent = 'Edit Vehicle';

                    document.getElementById('editModal').style.display = 'block';
                }
            });
    }

    function deleteVehicle(vehicle_id) {
        fetch('vehicle_operations.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ vehicle_id: vehicle_id })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Vehicle deleted successfully!');
                    location.reload();
                } else {
                    alert('Error deleting vehicle!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete vehicle.');
            });
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    document.getElementById('editForm').addEventListener('submit', function (e) {
        e.preventDefault();

        var formData = new FormData(this);
        var vehicleId = document.getElementById('vehicle_id').value;
        var method = vehicleId ? 'PUT' : 'POST'; // Use PUT for editing, POST for adding

        // Determine the URL based on the method
        var url = 'vehicle_operations.php'; // The URL remains the same for both cases

        var request = new XMLHttpRequest();
        request.open(method, url, true);

        // Set the content type header for JSON when the method is PUT
        if (method === 'PUT') {
            var data = {};
            formData.forEach(function (value, key) {
                data[key] = value; // Convert FormData into a simple object
            });

            // Set the request header for sending JSON
            request.setRequestHeader('Content-Type', 'application/json');

            // Send the data as a JSON string
            request.send(JSON.stringify(data));
        } else {
            // For POST, send the FormData as is
            request.send(formData);
        }

        // Handling the response from the server
        request.onreadystatechange = function () {
            if (request.readyState == 4 && request.status == 200) {
                var response = JSON.parse(request.responseText);
                if (response.success) {
                    alert('Vehicle saved successfully!');
                    closeEditModal(); // Close the modal after saving
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('Error saving vehicle!');
                }
            }
        };
    });
</script>
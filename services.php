<?php
// Include the database connection file
include('db_connection.php');

// Fetch service data from the database
$query = "
    SELECT 
        service_id, 
        service_name, 
        service_type, 
        description, 
        cost, 
        labor_time, 
        parts_needed, 
        service_status, 
        created_at, 
        updated_at
    FROM 
        services
";
$result = $conn->query($query);

// Check if there are any services in the database
if ($result->num_rows > 0) {
    // Service data found
    $services = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // No services found
    $services = [];
}

$conn->close();
?>

<h2>Service List</h2>

<button onclick="openAddModal()">Add New Service</button>

<!-- Table to display service data -->
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px; border-collapse: collapse;">
    <thead>
        <tr>
            <th>Service ID</th>
            <th>Service Name</th>
            <th>Service Type</th>
            <th>Description</th>
            <th>Cost</th>
            <th>Labor Time</th>
            <th>Parts Needed</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($services)): ?>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><?php echo $service['service_id']; ?></td>
                    <td><?php echo $service['service_name']; ?></td>
                    <td><?php echo $service['service_type']; ?></td>
                    <td><?php echo $service['description']; ?></td>
                    <td><?php echo $service['cost']; ?></td>
                    <td><?php echo $service['labor_time']; ?></td>
                    <td><?php echo $service['parts_needed']; ?></td>
                    <td><?php echo $service['service_status']; ?></td>
                    <td>
                        <button
                            style="text-decoration: none; color: white; background-color: blue; padding: 5px 10px; border-radius: 5px;"
                            onclick="openEditModal(<?php echo $service['service_id']; ?>)">Edit</button>
                        <button
                            style="text-decoration: none; color: white; background-color: red; padding: 5px 10px; border-radius: 5px;"
                            onclick="deleteService(<?php echo $service['service_id']; ?>)">Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" style="text-align: center;">No services found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Modal Form for Editing / Adding Services -->
<div id="editModal" style="display: none;">
    <div
        style="background-color: rgba(0, 0, 0, 0.5); width: 100%; height: 100%; position: fixed; top: 0; left: 0; display: flex; justify-content: center; align-items: center;">
        <div style="background-color: white; padding: 20px; width: 400px; border-radius: 5px;">
            <h3 id="modalTitle">Add New Service</h3>
            <form id="editForm" method="POST">
                <input type="hidden" name="service_id" id="service_id">
                <!-- For Edit, this field will contain the service ID -->

                <label for="service_name">Service Name:</label>
                <input type="text" name="service_name" id="service_name" required><br><br>

                <label for="service_type">Service Type:</label>
                <input type="text" name="service_type" id="service_type" required><br><br>

                <label for="description">Description:</label>
                <textarea name="description" id="description" required></textarea><br><br>

                <label for="cost">Cost:</label>
                <input type="text" name="cost" id="cost" required><br><br>

                <label for="labor_time">Labor Time:</label>
                <input type="text" name="labor_time" id="labor_time" required><br><br>

                <label for="parts_needed">Parts Needed:</label>
                <input type="text" name="parts_needed" id="parts_needed" required><br><br>

                <label for="service_status">Service Status:</label>
                <input type="text" name="service_status" id="service_status" required><br><br>

                <button type="submit">Save</button>
                <button type="button" onclick="closeEditModal()">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Open the Add Service Modal
    function openAddModal() {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('modalTitle').textContent = 'Add New Service';
        document.getElementById('editForm').reset();
        document.getElementById('service_id').value = '';
    }

    // Open the Edit Service Modal
    function openEditModal(service_id) {
        // Fetch existing service data via AJAX
        fetch('services_operations.php?service_id=' + service_id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate the form with existing data
                    document.getElementById('service_id').value = data.data.service_id;
                    document.getElementById('service_name').value = data.data.service_name;
                    document.getElementById('service_type').value = data.data.service_type;
                    document.getElementById('description').value = data.data.description;
                    document.getElementById('cost').value = data.data.cost;
                    document.getElementById('labor_time').value = data.data.labor_time;
                    document.getElementById('parts_needed').value = data.data.parts_needed;
                    document.getElementById('service_status').value = data.data.service_status;

                    // Change the modal title to 'Edit Service'
                    document.getElementById('modalTitle').textContent = 'Edit Service';

                    // Show the modal
                    document.getElementById('editModal').style.display = 'block';
                }
            });
    }

    // Delete Service
    function deleteService(service_id) {
        fetch('services_operations.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ service_id: service_id }) // Send service ID to the server
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Service deleted successfully!');
                    location.reload();  // Reload the page to reflect changes
                } else {
                    alert('Error deleting service!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete service.');
            });
    }

    // Close the modal
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Handle form submission (add or edit based on service_id)
    document.getElementById('editForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        var formData = new FormData(this);
        var serviceId = document.getElementById('service_id').value;
        var method = serviceId ? 'PUT' : 'POST'; // Use PUT for editing, POST for adding

        // Determine the URL based on the method
        var url = 'services_operations.php'; // The URL remains the same for both cases

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
                    alert('Service saved successfully!');
                    closeEditModal(); // Close the modal after saving
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('Error saving service!');
                }
            }
        };
    });
</script>
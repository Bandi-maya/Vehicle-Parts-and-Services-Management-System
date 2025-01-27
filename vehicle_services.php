<?php

include('db_connection.php');

if ($_SESSION['role'] === 'customer') {
    $customer_id = $_SESSION['customer_id'];
    $query = "
        SELECT 
            vehicle_services.service_id, 
            vehicle_services.id, 
            vehicle_services.vehicle_id, 
            services.service_name, 
            services.service_type, 
            vehicle_services.service_cost, 
            vehicle_services.labor_time, 
            vehicle_services.parts_needed, 
            vehicle_services.service_date,
            customers.customer_id,
            customers.first_name,
            customers.last_name,
            customers.phone_number,
            customers.address,
            vehicles.make AS vehicle_make, 
            vehicles.model AS vehicle_model
        FROM 
            vehicle_services
        JOIN 
            services ON vehicle_services.service_id = services.service_id
        JOIN 
            vehicles ON vehicle_services.vehicle_id = vehicles.vehicle_id
        JOIN 
            customers ON vehicles.customer_id = customers.customer_id
        WHERE customers.customer_id = $customer_id
    ";
} else {
    $query = "
        SELECT 
            vehicle_services.service_id, 
            vehicle_services.id, 
            vehicle_services.vehicle_id, 
            services.service_name, 
            services.service_type, 
            vehicle_services.service_cost, 
            vehicle_services.labor_time, 
            vehicle_services.parts_needed, 
            vehicle_services.service_date,
            customers.customer_id,
            customers.first_name,
            customers.last_name,
            customers.phone_number,
            customers.address,
            vehicles.make AS vehicle_make, 
            vehicles.model AS vehicle_model
        FROM 
            vehicle_services
        JOIN 
            services ON vehicle_services.service_id = services.service_id
        JOIN 
            vehicles ON vehicle_services.vehicle_id = vehicles.vehicle_id
        JOIN 
            customers ON vehicles.customer_id = customers.customer_id
    ";
}

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $services = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $services = [];
}

$conn->close();
?>

<h2>Vehicle Services List</h2>

<?php
if ($_SESSION['role'] !== 'customer') {
    echo '<button onclick="openAddModal()">Add New Service</button>';
}
?>

<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px; border-collapse: collapse;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Vehicle</th>
            <th>Service Name</th>
            <th>Service Type</th>
            <th>Service Cost</th>
            <th>Labor Time</th>
            <th>Parts Needed</th>
            <th>Service Date</th>
            <?php
            if ($_SESSION['role'] !== 'customer') {
                echo '<th>Actions</th>';
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($services)): ?>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><?php echo $service['id']; ?></td>
                    <td><?php echo $service['vehicle_make'] . ' ' . $service['vehicle_model']; ?></td>
                    <td><?php echo $service['service_name']; ?></td>
                    <td><?php echo $service['service_type']; ?></td>
                    <td><?php echo $service['service_cost']; ?></td>
                    <td><?php echo $service['labor_time']; ?></td>
                    <td><?php echo $service['parts_needed']; ?></td>
                    <td><?php echo $service['service_date']; ?></td>
                    <?php
                    if ($_SESSION['role'] !== 'customer') {
                        echo '<td>
                                <button
                                    style="text-decoration: none; color: white; background-color: blue; padding: 5px 10px; border-radius: 5px;"
                                    onclick="openEditModal(' . $service['id'] . ')">Edit</button>
                                <button
                                    style="text-decoration: none; color: white; background-color: red; padding: 5px 10px; border-radius: 5px;"
                                    onclick="deleteService(' . $service['id'] . ')">Delete</button>
                            </td>';
                    }
                    ?>

                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" style="text-align: center;">No services found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div id="editModal" style="display: none;">
    <div
        style="background-color: rgba(0, 0, 0, 0.5); width: 100%; height: 100%; position: fixed; top: 0; left: 0; display: flex; justify-content: center; align-items: center;">
        <div style="background-color: white; padding: 20px; width: 400px; border-radius: 5px;">
            <h3 id="modalTitle">Add New Service</h3>
            <form id="editForm" method="POST">
                <input type="hidden" name="id" id="id">

                <label for="vehicle_id">Vehicle ID:</label>
                <input type="text" name="vehicle_id" id="vehicle_id" required><br><br>

                <label for="service_name">Service Id:</label>
                <input type="text" name="service_id" id="service_id" required><br><br>

                <label for="service_cost">Service Cost:</label>
                <input type="number" name="service_cost" id="service_cost" required><br><br>

                <label for="labor_time">Labor Time (hours):</label>
                <input type="number" name="labor_time" id="labor_time" required><br><br>

                <label for="parts_needed">Parts Needed:</label>
                <textarea name="parts_needed" id="parts_needed" required></textarea><br><br>

                <label for="service_date">Service Date:</label>
                <input type="date" name="service_date" id="service_date" ><br><br>

                <button type="submit">Save</button>
                <button type="button" onclick="closeEditModal()">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('modalTitle').textContent = 'Add New Service';
        document.getElementById('editForm').reset();
        document.getElementById('id').value = '';
    }

    function openEditModal(service_id) {
        fetch('vehicle_service_operations.php?id=' + service_id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('id').value = service_id;
                    document.getElementById('service_id').value = data.data.service_id;
                    document.getElementById('vehicle_id').value = data.data.vehicle_id;
                    document.getElementById('service_cost').value = data.data.service_cost;
                    document.getElementById('labor_time').value = data.data.labor_time;
                    document.getElementById('parts_needed').value = data.data.parts_needed;
                    document.getElementById('service_date').value = data.data.service_date;

                    document.getElementById('modalTitle').textContent = 'Edit Service';

                    document.getElementById('editModal').style.display = 'block';
                }
            });
    }

    function deleteService(service_id) {
        fetch('vehicle_service_operations.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ service_id: service_id })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Service deleted successfully!');
                    location.reload();
                } else {
                    alert('Error deleting service!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete service.');
            });
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    document.getElementById('editForm').addEventListener('submit', function (e) {
        e.preventDefault();

        var formData = new FormData(this);
        var serviceId = document.getElementById('id').value;
        var method;
        if (serviceId) {
            method = 'PUT'
        }
        else {
            method = 'POST'
        }

        var url = 'vehicle_service_operations.php';

        var request = new XMLHttpRequest();
        request.open(method, url, true);

        if (method === 'PUT') {
            var data = {};
            formData.forEach(function (value, key) {
                data[key] = value;
            });

            request.setRequestHeader('Content-Type', 'application/json');

            request.send(JSON.stringify(data));
        } else {
            request.send(formData);
        }

        request.onreadystatechange = function () {
            if (request.readyState == 4 && request.status == 200) {
                var response = JSON.parse(request.responseText);
                if (response.success) {
                    alert('Service saved successfully!');
                    closeEditModal();
                    location.reload();
                } else {
                    alert('Error saving service!');
                }
            }
        };
    });
</script>
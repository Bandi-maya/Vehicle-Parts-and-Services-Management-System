<?php
// Include the database connection file 
include('db_connection.php');
// Fetch customer data from the database
$query = "
    SELECT 
        customers.customer_id, 
        customers.first_name, 
        customers.last_name, 
        customers.address, 
        customers.phone_number, 
        customers.preferred_contact_method, 
        user_logins.user_id, 
        user_logins.username, 
        user_logins.password, 
        user_logins.role, 
        user_logins.created_at, 
        user_logins.updated_at
    FROM 
        customers
    JOIN 
        user_logins ON customers.customer_id = user_logins.customer_id
";
$result = $conn->query($query);

// Check if there are any customers in the database
if ($result->num_rows > 0) {
    // Customer data found
    $customers = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // No customers found
    $customers = [];
}

$conn->close();
?>

<h2>Customer List</h2>

<button onclick="openAddModal()">Add New Customer</button>

<!-- Table to display customer data -->
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px; border-collapse: collapse;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Firstname</th>
            <th>Lastname</th>
            <th>Phone no</th>
            <th>Address</th>
            <th>Preferred contact method</th>
            <th>Joined Date</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($customers)): ?>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo $customer['customer_id']; ?></td>
                    <td><?php echo $customer['username']; ?></td>
                    <td><?php echo $customer['first_name']; ?></td>
                    <td><?php echo $customer['last_name']; ?></td>
                    <td><?php echo $customer['phone_number']; ?></td>
                    <td><?php echo $customer['address']; ?></td>
                    <td><?php echo $customer['preferred_contact_method']; ?></td>
                    <td><?php echo $customer['created_at']; ?></td>
                    <td>
                        <button
                            style="text-decoration: none; color: white; background-color: blue; padding: 5px 10px; border-radius: 5px;"
                            onclick="openEditModal(<?php echo $customer['customer_id']; ?>)">Edit</button>
                        <button
                            style="text-decoration: none; color: white; background-color: red; padding: 5px 10px; border-radius: 5px;"
                            onclick="deleteCustomer(<?php echo $customer['customer_id']; ?>)">Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">No customers found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Modal Form for Editing / Adding Staff -->
<div id="editModal" style="display: none;">
    <div
        style="background-color: rgba(0, 0, 0, 0.5); width: 100%; height: 100%; position: fixed; top: 0; left: 0; display: flex; justify-content: center; align-items: center;">
        <div style="background-color: white; padding: 20px; width: 400px; border-radius: 5px;">
            <h3 id="modalTitle">Add New Customer</h3>
            <form id="editForm" method="POST">
                <input type="hidden" name="customer_id" id="customer_id">
                <!-- For Edit, this field will contain the staff ID -->

                <!-- Show password and confirm password fields only for Add -->
                <div id="passwordFields" style="display: block;">
                </div>

                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required><br><br>

                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" id="first_name" required><br><br>

                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" id="last_name" required><br><br>

                <label for="phone_number">Phone Number:</label>
                <input type="text" name="phone_number" id="phone_number" required><br><br>

                <label for="address">Address:</label>
                <textarea name="address" id="address" required></textarea><br><br>

                <label for="preferred_contact_method">Preferred contact method:</label>
                <input type="text" name="preferred_contact_method" id="preferred_contact_method" required><br><br>

                <button type="submit">Save</button>
                <button type="button" onclick="closeEditModal()">Cancel</button>
            </form>
        </div>
    </div>
</div>
<script>

    // Open the Add Staff Modal
    function openAddModal() {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('modalTitle').textContent = 'Add New Customer';
        document.getElementById('editForm').reset();
        document.getElementById('customer_id').value = '';
        document.getElementById('passwordFields').innerHTML = `<label for="password">Password:</label>
                    <input type="password" name="password" id="password" required><br><br>

                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required><br><br>`
        // Show password fields when adding staff
        document.getElementById('passwordFields').style.display = 'block';
    }

    // Open the Edit Staff Modal
    function openEditModal(staff_id) {
        // Fetch existing staff data via AJAX
        fetch('users.php?customer_id=' + staff_id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate the form with existing data
                    document.getElementById('customer_id').value = data.data.customer_id;
                    document.getElementById('first_name').value = data.data.first_name;
                    document.getElementById('last_name').value = data.data.last_name;
                    document.getElementById('phone_number').value = data.data.phone_number;
                    document.getElementById('address').value = data.data.address;
                    document.getElementById('username').value = data.data.username;
                    document.getElementById('preferred_contact_method').value = data.data.preferred_contact_method;

                    // Hide the password fields when editing data
                    document.getElementById('passwordFields').style.display = 'none';

                    // Change the modal title to 'Edit Staff'
                    document.getElementById('modalTitle').textContent = 'Edit Staff';

                    // Show the modal
                    document.getElementById('editModal').style.display = 'block';
                }
            });
    }

    // Open the Edit Staff Modal
    function deleteCustomer(staff_id) {
        fetch('users.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: staff_id }) // Send staff ID to the server
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Customer member deleted successfully!');
                    location.reload();  // Reload the page to reflect changes
                } else {
                    alert('Error deleting customer!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete customer.');
            });
    }

    // Close the modal
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Handle form submission (add or edit based on staff_id)
    document.getElementById('editForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        var formData = new FormData(this);
        var staffId = document.getElementById('customer_id').value;
        var method = staffId ? 'PUT' : 'POST'; // Use PUT for editing, POST for adding

        // Determine the URL based on the method
        var url = 'users.php'; // The URL remains the same for both cases

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
                    alert('Customer saved successfully!');
                    closeEditModal(); // Close the modal after saving
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('Error saving customer!');
                }
            }
        };
    });
</script>
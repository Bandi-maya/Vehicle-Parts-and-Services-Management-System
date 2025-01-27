<?php
// Include the database connection file
include 'auth.php';
include('db_connection.php');

// SQL query to fetch staff and login details by joining the staff and user_logins tables
$query = "
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
";

// Execute the query
$result = $conn->query($query);

// Check if there are any results
if ($result->num_rows > 0) {
    // Fetch all the results into an associative array
    $staff_data = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // No data found
    $staff_data = [];
}

$conn->close();
?>

<h2>Staff Information with Login Details</h2>

<button onclick="openAddModal()">Add New Staff</button>

<!-- Table to display staff data -->
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px; border-collapse: collapse;">
    <thead>
        <tr>
            <th>Staff ID</th>
            <th>Username</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Role</th>
            <th>Address</th>
            <th>Hire Date</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($staff_data)): ?>
            <?php foreach ($staff_data as $entry): ?>
                <tr>
                    <td><?php echo $entry['staff_id']; ?></td>
                    <td><?php echo $entry['username']; ?></td>
                    <td><?php echo $entry['first_name']; ?></td>
                    <td><?php echo $entry['last_name']; ?></td>
                    <td><?php echo $entry['email']; ?></td>
                    <td><?php echo $entry['phone_number']; ?></td>
                    <td><?php echo $entry['role']; ?></td>
                    <td><?php echo $entry['address']; ?></td>
                    <td><?php echo $entry['hire_date']; ?></td>
                    <td><?php echo $entry['status']; ?></td>
                    <td><?php echo $entry['staff_created_at']; ?></td>
                    <td><?php echo $entry['login_role']; ?></td>
                    <td><?php echo $entry['login_created_at']; ?></td>
                    <td>
                        <button
                            style="text-decoration: none; color: white; background-color: blue; padding: 5px 10px; border-radius: 5px;"
                            onclick="openEditModal(<?php echo $entry['staff_id']; ?>)">Edit</button>
                        <button
                            style="text-decoration: none; color: white; background-color: red; padding: 5px 10px; border-radius: 5px;"
                            onclick="deleteStaff(<?php echo $entry['staff_id']; ?>)">Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="15">No staff found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<!-- Modal Form for Editing / Adding Staff -->
<div id="editModal" style="display: none;">
    <div
        style="background-color: rgba(0, 0, 0, 0.5); width: 100%; height: 100%; position: fixed; top: 0; left: 0; display: flex; justify-content: center; align-items: center;">
        <div style="background-color: white; padding: 20px; width: 400px; border-radius: 5px;">
            <h3 id="modalTitle">Add New Staff</h3>
            <form id="editForm" method="POST">
                <input type="hidden" name="staff_id" id="staff_id">
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

                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required><br><br>

                <label for="phone_number">Phone Number:</label>
                <input type="text" name="phone_number" id="phone_number" required><br><br>

                <label for="role">Role:</label>
                <input type="text" name="role" id="role" required><br><br>

                <label for="address">Address:</label>
                <textarea name="address" id="address" required></textarea><br><br>

                <label for="login_role">Hire date:</label>
                <input type="date" name="hire_date" id="hire_date" required><br><br>

                <label for="status">Status:</label>
                <input type="status" name="status" id="status" required><br><br>

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
        document.getElementById('modalTitle').textContent = 'Add New Staff';
        document.getElementById('editForm').reset();
        document.getElementById('staff_id').value = '';
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
        fetch('staff.php?staff_id=' + staff_id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate the form with existing data
                    document.getElementById('staff_id').value = data.data.staff_id;
                    document.getElementById('first_name').value = data.data.first_name;
                    document.getElementById('last_name').value = data.data.last_name;
                    document.getElementById('email').value = data.data.email;
                    document.getElementById('phone_number').value = data.data.phone_number;
                    document.getElementById('role').value = data.data.role;
                    document.getElementById('address').value = data.data.address;
                    document.getElementById('username').value = data.data.username;
                    document.getElementById('hire_date').value = data.data.hire_date;
                    document.getElementById('status').value = data.data.status;

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
    function deleteStaff(staff_id) {
        fetch('staff.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: staff_id })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('staff member deleted successfully!');
                    location.reload();  // Reload the page to reflect changes
                } else {
                    alert('Error deleting staff!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete staff.');
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
        var staffId = document.getElementById('staff_id').value;
        var method = staffId ? 'PUT' : 'POST'; // Use PUT for editing, POST for adding

        // Determine the URL based on the method
        var url = 'staff.php'; // The URL remains the same for both cases

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
                    alert('Staff saved successfully!');
                    closeEditModal(); // Close the modal after saving
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('Error saving staff!');
                }
            }
        };
    });
</script>
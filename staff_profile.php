<?php
include 'auth.php';
?>

<div style="background-color: white; padding: 20px; width: 400px; border-radius: 5px;">
    <h3 id="modalTitle">Profile</h3>
    <form id="editForm" method="POST">
        <?php echo $_SESSION['staff_id'] ?? null; ?>
        <input type="hidden" name="staff_id" id="staff_id" value="<?php echo $_SESSION['staff_id'] ?? null; ?>">

        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br><br>

        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" id="first_name" required><br><br>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" required><br><br>

        <label for="email">Email:</label>
        <input type="text" name="email" id="email" required><br><br>

        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number" id="phone_number" required><br><br>

        <label for="role">Role:</label>
        <input type="text" name="role" id="role" required><br><br>

        <label for="address">Address:</label>
        <textarea name="address" id="address" required></textarea><br><br>

        <label for="hire_date">Hire Date:</label>
        <input type="date" name="hire_date" id="hire_date" required><br><br>

        <label for="status">Status:</label>
        <input type="text" name="status" id="status" required><br><br>

        <button type="submit">Save</button>
        <button type="button" onclick="cancelChanges()">Cancel</button>
    </form>
</div>
<script>
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
                    document.getElementById('phone_number').value = data.data.phone_number;
                    document.getElementById('email').value = data.data.email;
                    document.getElementById('status').value = data.data.status;
                    document.getElementById('address').value = data.data.address;
                    document.getElementById('username').value = data.data.username;
                    document.getElementById('role').value = data.data.role;
                    document.getElementById('hire_date').value = data.data.hire_date;

                    // Hide the password fields when editing data
                    document.getElementById('passwordFields').style.display = 'none';

                    // Change the modal title to 'Edit Staff'
                    document.getElementById('modalTitle').textContent = 'Edit Staff';

                    // Show the modal
                    document.getElementById('editModal').style.display = 'block';
                }
            });
    }
    openEditModal(<?php echo $_SESSION['staff_id'] ?>)

    // Handle form submission (add or edit based on staff_id)
    document.getElementById('editForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        var formData = new FormData(this);
        var staffId = document.getElementById('staff_id').value;
        var method = 'PUT'

        var url = 'staff.php';

        var request = new XMLHttpRequest();
        request.open(method, url, true);

        if (staffId) {
            var data = {};
            formData.forEach(function (value, key) {
                data[key] = value; // Convert FormData into a simple object
            });

            // Set the request header for sending JSON
            request.setRequestHeader('Content-Type', 'application/json');

            // Send the data as a JSON string
            request.send(JSON.stringify(data));

            // Handling the response from the server
            request.onreadystatechange = function () {
                if (request.readyState == 4 && request.status == 200) {
                    var response = JSON.parse(request.responseText);
                    if (response.success) {
                        alert('Profile saved successfully!');
                        closeEditModal();
                        location.reload();
                    } else {
                        alert('Error saving profile!');
                    }
                }
            };
        }
    });
</script>
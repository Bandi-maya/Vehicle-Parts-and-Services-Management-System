<?php
include 'auth.php';
?>

<div class="profile-form" style="padding: 20px; width: 400px; border-radius: 5px;">
    <h3 id="modalTitle">Profile</h3>
    <form id="editForm" method="POST">
        <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $_SESSION['customer_id'] ?? null; ?>">

        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" name="username" id="username" required>
        </div>

        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" class="form-control" name="first_name" id="first_name" required>
        </div>

        <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" class="form-control" name="last_name" id="last_name" required>
        </div>

        <div class="form-group">
            <label for="phone_number">Phone Number:</label>
            <input type="text" class="form-control" name="phone_number" id="phone_number" required>
        </div>

        <div class="form-group">
            <label for="address">Address:</label>
            <textarea class="form-control" name="address" id="address" required></textarea>
        </div>

        <div class="form-group">
            <label for="preferred_contact_method">Preferred contact method:</label>
            <input type="text" class="form-control" name="preferred_contact_method" id="preferred_contact_method" required>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary ml-2" onclick="cancelChanges()">Cancel</button>
    </form>
</div>

<script>
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

openEditModal(<?php echo $_SESSION['customer_id'] ?>)

// Handle form submission (add or edit based on staff_id)
document.getElementById('editForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent default form submission

    var formData = new FormData(this);
    var staffId = document.getElementById('customer_id').value;
    var method = 'PUT';

    var url = 'users.php';

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

<style>
/* Add styles for dark mode */
.dark-mode .profile-form {
    background-color: #1e1e1e; /* Dark background for profile form */
    color: #ffffff; /* White text color */
}

.dark-mode .profile-form input,
.dark-mode .profile-form textarea,
.dark-mode .profile-form button {
    background-color: #333; /* Darker background for inputs */
    color: #ffffff; /* White text color */
    border: 1px solid #444; /* Border color */
}

.dark-mode .profile-form button:hover {
    background-color: #555; /* Darker on hover */
}

/* Ensure labels are visible in dark mode */
.dark-mode .profile-form label {
    color: #ffffff; /* White text color for labels */
}
</style>
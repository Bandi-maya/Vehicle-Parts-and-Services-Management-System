<?php
session_start();
include 'auth.php';
$content = 'home';
if (isset($_GET['section'])) {
    $content = $_GET['section'];
}

function loadContent($section)
{
    switch ($section) {
        case 'profile':
            if ($_SESSION['role'] === 'customer') {
                include 'profile.php';
            } else {
                include 'staff_profile.php';
            }
            break;
        case 'customers':
            include 'customers.php';
            break;
        case 'staff':
            include 'staffusers.php';
            break;
        case 'services':
            include 'services.php';
            break;
        case 'vehicle':
            include 'vehicles.php';
            break;
        case 'vehicle_services':
            include 'vehicle_services.php';
            break;
        default:
            echo '<h2>Welcome to the Vehicle Service Management System!</h2>';
            echo '<p>Select a section from the sidebar to view more details.</p>';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Service Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    
    <!-- Import Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            padding: 0;
            margin: 0;
            font-family: 'Poppins', sans-serif; /* Set Poppins as the default font */
            transition: background-color 0.3s, color 0.3s;
        }

        .header {
            height: 60px;
            text-align: center;
            padding: 15px 0;
            background-color: #007bff; /* Bootstrap primary color */
            border-bottom: 2px solid #0056b3; /* Darker border for header */
        }

        .sidebar {
            width: 250px;
            float: left;
            padding: 10px;
            border-right: 2px solid #ccc; /* Light gray border for sidebar */
        }

        .sider-element {
            margin-bottom: 5px;
        }

        .sider-element-inner {
            height: 50px;
            padding: 10px 5px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }

        .sider-element-inner:hover {
            background-color: #d3d3d3; /* Light grey on hover */
        }

        a {
            list-style: none;
        }

        .content {
            margin-left: 270px; /* Adjusted for sidebar width */
            padding: 20px;
        }

        .dark-mode {
            background-color: #121212;
            color: #ffffff;
        }

        .dark-mode .sider-element-inner {
            background-color: #333;
        }

        .dark-mode .sider-element-inner:hover {
            background-color: #555; /* Darker grey on hover in dark mode */
        }

        .dark-mode .sider-element-inner i {
           color: #ffffff; /* Change icon color in dark mode */
       }

       .sider-element-inner i {
           color: #000; /* Default icon color in light mode */
           margin-right: 10px; /* Space between icon and text */
       }

       .toggle-icon {
           cursor: pointer;
           font-size: 30px;
           position: fixed;
           top: 15px;
           right: 25px;
           z-index: 1000;
           transition: transform 0.5s;
       }

       .toggle-icon.dark {
           transform: rotate(360deg);
           transition: transform 0.5s;
       }

       /* Floating animation for content */
       @keyframes floatIn {
           from {
               opacity: 0; 
               transform: translateY(100%);
           }
           to {
               opacity: 1; 
               transform: translateY(0);
           }
       }

       .content > * {
           animation: floatIn 0.5s ease forwards; /* Apply animation to all direct children of content */
       }
   </style>
</head>

<body>
   <div class="toggle-icon" id="toggleIcon"><i class='bx bx-sun'></i></div>

   <div class="header">
       <h1 class="text-white">Vehicle Service Management System</h1>
   </div>

   <div class="sidebar">
       <div class="sider-element">
           <a href="?section=profile" class="sider-element-inner"><i class='bx bx-user'></i> Profile <?php echo $_SESSION['role'] ?></a>
       </div>

       <?php if ($_SESSION['role'] !== 'customer') { ?>
           <div class="sider-element">
               <a href="?section=customers" class="sider-element-inner"><i class='bx bx-group'></i> Customers</a>
           </div>
       <?php } ?>

       <?php if ($_SESSION['role'] === 'admin') { ?>
           <div class="sider-element">
               <a href="?section=staff" class="sider-element-inner"><i class='bx bx-user-check'></i> Staff</a>
           </div>

           <div class="sider-element">
               <a href="?section=services" class="sider-element-inner"><i class='bx bx-cog'></i> Services</a>
           </div>
       <?php } ?>

       <div class="sider-element">
           <a href="?section=vehicle" class="sider-element-inner"><i class='bx bx-car'></i> Vehicle</a>
       </div>

       <div class="sider-element">
           <a href="?section=vehicle_services" class="sider-element-inner"><i class='bx bx-wrench'></i> Vehicle Services</a>
       </div>
   </div>

   <div class="content">
       <?php loadContent($content); ?>
   </div>

   <script>
       const toggleIcon = document.getElementById('toggleIcon');

       // Check localStorage for dark mode preference
       if (localStorage.getItem('darkMode') === 'enabled') {
          document.body.classList.add('dark-mode');
          toggleIcon.innerHTML = "<i class='bx bx-moon'></i>"; // Moon icon for dark mode
          toggleIcon.classList.add('dark');
      } else {
          toggleIcon.innerHTML = "<i class='bx bx-sun'></i>"; // Sun icon for light mode
      }

      toggleIcon.addEventListener('click', () => {
          document.body.classList.toggle('dark-mode');
          toggleIcon.classList.toggle('dark');

          // Change icon based on theme
          if (document.body.classList.contains('dark-mode')) {
              toggleIcon.innerHTML = "<i class='bx bx-moon'></i>"; // Moon icon for dark mode
              localStorage.setItem('darkMode', 'enabled'); // Save preference
          } else {
              toggleIcon.innerHTML = "<i class='bx bx-sun'></i>"; // Sun icon for light mode
              localStorage.setItem('darkMode', null); // Remove preference
          }
      });
   </script>
</body>

</html>
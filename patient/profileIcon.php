<?php
// Retrieve patient ID from session
$patient_id = $_SESSION['patient_id'];

// Fetch patient name from the database
$query = "SELECT name FROM patient WHERE patient_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$stmt->bind_result($patient_name);
$stmt->fetch();
$stmt->close();
?>
<style>
    .profileIcon {
        position: absolute;
        right: 10px;
        /* Margin from the right edge */
        top: 20%;
        /* Adjust for vertical alignment */
        transform: translateY(-50%);
        /* Center vertically */
        z-index: 1000;
        /* Ensure it appears above other elements */
    }

    .profile-icon {
        background-color: #007b83;
        color: #fff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2em;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .profile-icon:hover {
        transform: scale(1.1);
    }

    .profile-dropdown {
        width: 200px;
        display: none;
        /* Hidden by default */
        position: absolute;
        right: 0;
        /* Align to the right of the profile icon */
        top: 50px;
        /* Adjust the vertical position as needed */
        background: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        overflow: hidden;
        z-index: 1000;
        /* Ensure dropdown appears above other content */
    }

    .profile-dropdown a {
        display: block;
        padding: 10px 20px;
        color: #333;
        text-decoration: none;
    }

    .profile-dropdown a:hover {
        background-color: #007b83;
        color: #fff;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<div class="profileIcon">
    <div class="profile-icon" onclick="toggleDropdown()">
        <?= strtoupper($patient_name[0]); ?>
    </div>
    <div id="profileDropdown" class="profile-dropdown">
        <a href="dashboard.php"><i class="fas fa-user-circle"></i> Dashboard</a>
        <a href="edit_profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    document.addEventListener('click', function(event) {
        const profileIcon = document.querySelector('.profile-icon');
        const dropdown = document.getElementById('profileDropdown');
        if (!profileIcon.contains(event.target) && dropdown.style.display === 'block') {
            dropdown.style.display = 'none'; // Close dropdown if clicking outside
        }
    });
</script>
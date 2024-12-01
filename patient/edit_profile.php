<?php
// Check if the session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("../include/db.php");


// Ensure the patient is logged in
if (!isset($_SESSION['patient_id']) || ($_SESSION['role'] !== 'patient')) {
    header("Location: ../login.php");
    exit;
}

$patient_id = $_SESSION['patient_id'];

// Fetch patient data
$query = "SELECT name, email, phone, blood_group FROM patient WHERE patient_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $blood_group);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle profile update
    $new_blood_group = $_POST['blood_group'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if ($new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE patient SET blood_group = ?, password = ? WHERE patient_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $new_blood_group, $hashed_password, $patient_id);
        $update_stmt->execute();
        $update_stmt->close();
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Passwords do not match!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        b.alert {
            margin-top: 20px;
        }

        .navbar {
            color: white;
        }
    </style>
</head>

<body>
    <?php

    include "dash_design.php"

    ?>
    <div class="edit-profile-container">

        <h2>Edit Profile</h2>

        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <form action="edit_profile.php" method="post">
            <div class="mb-3">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name); ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email); ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone); ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="blood_group">Blood Group</label>
                <select id="blood_group" name="blood_group" class="form-control">
                    <option value="" disabled>Select your blood group</option>
                    <?php
                    $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                    foreach ($blood_groups as $group) {
                        echo "<option value='$group' " . ($group == $blood_group ? 'selected' : '') . ">$group</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="new_password">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password">
            </div>

            <div class="mb-3">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
            </div>

            <button type="submit" class="btn-save">Save Changes</button>
        </form>
    </div>

    <script>
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            if (confirmPassword !== newPassword) {
                this.setCustomValidity("Passwords don't match");
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>

</html>
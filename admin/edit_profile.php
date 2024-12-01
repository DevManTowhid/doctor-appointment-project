<?php
session_start();
include '../include/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch the admin's current details
$admin_id = $_SESSION['admin_id'];
$query = "SELECT * FROM admin WHERE admin_id = '$admin_id' LIMIT 1";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 1) {
    $admin = mysqli_fetch_assoc($result);
} else {
    header('Location: dashboard.php');  // Redirect if admin not found
    exit();
}

// Update the profile if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $blood_group = mysqli_real_escape_string($conn, $_POST['blood_group']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Password update logic
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if the passwords match
    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match!";
    } else {
        // If passwords match, hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update query (update all fields including password if new password is set)
        $update_query = "UPDATE admin SET name='$name', email='$email', phone='$phone', blood_group='$blood_group', gender='$gender', address='$address'";

        if (!empty($new_password)) {
            $update_query .= ", password='$hashed_password'"; // Only update password if a new password is provided
        }
        $update_query .= " WHERE admin_id='$admin_id'";

        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success_message'] = "Profile updated successfully!";
            header('Location: edit_profile.php'); // Reload page after successful update
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating profile. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="dash_styling.css">
    <style>
        .alert {
            margin-top: 20px;
        }

        .navbar {
            color: white;
        }
    </style>
</head>

<body>
    <?php
    include('dash_design.php');
    ?>

    <main>
        <div class="container-fluid">
            <h2>Update Your Profile</h2>

            <!-- Display Success or Error Message -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']); ?>
                </div>
            <?php elseif (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Update Form -->
            <form action="edit_profile.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="blood_group" class="form-label">Blood Group</label>
                    <input type="text" class="form-control" id="blood_group" name="blood_group" value="<?php echo htmlspecialchars($admin['blood_group']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="male" <?php echo ($admin['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($admin['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                        <option value="other" <?php echo ($admin['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($admin['address']); ?></textarea>
                </div>

                <!-- Password Update Fields -->
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                </div>
                <div class="mb-3">
                    <input type="checkbox" id="show_password">
                    <label for="show_password">Show Password</label>
                </div>

                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </main>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Admin Panel. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
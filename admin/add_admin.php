<?php
session_start();
include('../include/db.php');

// Check if the admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== "admin") {
    header('Location: ../login.php');
    exit();
}

// Handle form submission to add admin
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $blood_group = mysqli_real_escape_string($conn, $_POST['blood_group']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];  // Initialize error array

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // Check if the email already exists
    $email_check_query = "SELECT * FROM admin WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $email_check_query);
    if (mysqli_num_rows($result) > 0) {
        $errors[] = "The email address is already registered.";
    }

    // Validate password confirmation
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // If there are no errors, proceed with insertion
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert new admin
        $query = "INSERT INTO admin (name, password, email, phone, blood_group, gender, address) 
                  VALUES ('$name', '$hashed_password', '$email', '$phone', '$blood_group', '$gender', '$address')";
        if (mysqli_query($conn, $query)) {
            $success_message = "New admin added successfully.";
        } else {
            $errors[] = "Error: " . mysqli_error($conn);
        }
    }
}

// Fetch existing admins
$admins_query = "SELECT * FROM admin";
$admins_result = mysqli_query($conn, $admins_query);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="dash_styling.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .form-container,
        .table-container {
            max-width: 700px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-container h2,
        .table-container h3 {
            text-align: center;
        }
    </style>
</head>

<body>
    <?php include('dash_design.php'); ?>

    <div class="container">
        <!-- Form to Add Admin -->
        <div class="form-container">
            <h2>Add Admin</h2>
            <?php
            if (isset($success_message)) echo "<div class='alert alert-success'>$success_message</div>";
            if (isset($error_message)) echo "<div class='alert alert-danger'>$error_message</div>";
            ?>

            <form action="add_admin.php" method="POST">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone (+880...)</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="blood_group">Blood Group</label>
                    <select name="blood_group" class="form-control" required>
                        <option disabled selected>Select blood group</option>
                        <option>A+</option>
                        <option>A-</option>
                        <option>B+</option>
                        <option>B-</option>
                        <option>AB+</option>
                        <option>AB-</option>
                        <option>O+</option>
                        <option>O-</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea class="form-control" id="address" name="address" required></textarea>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="mb-3">
                    <input type="checkbox" id="show_password">
                    <label for="show_password">Show Password</label>
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Submit</button>

            </form>
        </div>

        <!-- Table of Existing Admins -->
        <div class=" table-responsive mt-4">
            <h3>Current Admins</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Blood Group</th>
                        <th>Gender</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($admins_result)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['blood_group']); ?></td>
                            <td><?php echo htmlspecialchars($row['gender']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("show_password").addEventListener("change", function() {
            const passwordField = document.getElementById("password");
            const confirmPasswordField = document.getElementById("confirm_password");

            if (this.checked) {
                passwordField.type = "text";
                confirmPasswordField.type = "text";
            } else {
                passwordField.type = "password";
                confirmPasswordField.type = "password";
            }
        });
    </script>
</body>

</html>
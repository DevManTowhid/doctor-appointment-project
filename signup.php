<!-- original code  -->

<?php
include 'include/db.php';

// Initialize variables for error messages
$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collecting form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $birthdate = $_POST['birthdate'];
    $blood_group = $_POST['blood_group'];
    $address = trim($_POST['address']);
    $high_bp = isset($_POST['high_bp']) ? 1 : 0;
    $diabetes = isset($_POST['diabetes']) ? 1 : 0;
    $gender = trim($_POST['gender']);

    // Form validation
    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password) || empty($birthdate) || empty($blood_group) || empty($address) || empty($gender)) {
        $errors[] = 'All fields are required.';
    }
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check if email already exists
    if (empty($errors)) {
        $query = "SELECT * FROM patient WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = 'Email already exists.';
        }
    }

    // If no errors, insert data
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert_query = "INSERT INTO patient (name, email, phone, password, birthdate, blood_group, address, high_bp, diabetes, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("ssssssssss", $name, $email, $phone, $hashed_password, $birthdate, $blood_group, $address, $high_bp, $diabetes, $gender);

        if ($insert_stmt->execute()) {
            $success = 'Signup successful! You can now log in.';
        } else {
            $errors[] = 'An error occurred during signup. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Signup | Doctor Appointment System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">

    <style>
        body {
            background: linear-gradient(to right, #00c6ff, #0072ff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .card {
            max-width: 600px;
            border-radius: 15px;
            box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .card-header {
            border-radius: 15px 15px 0 0;
            background: #0072ff;
            color: #fff;
            text-align: center;
            padding: 20px;
            font-size: 1.25rem;
        }

        .btn-primary {
            background-color: #0072ff;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #005bb5;
        }

        .text-primary {
            color: #0072ff !important;
        }

        .intl-tel-input {
            width: 100%;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0 fw-bold">Patient Signup</h3>
                    </div>
                    <div class="card-body p-5">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="mt-4">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name:</label>
                                <input type="text" name="name" class="form-control" id="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" name="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number:</label>
                                <input type="tel" name="phone" class="form-control" id="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" name="password" class="form-control" id="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password:</label>
                                <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                            </div>
                            <div class="mb-3">
                                <input type="checkbox" id="show_password">
                                <label for="show_password">Show Password</label>
                            </div>

                            <div class="mb-3">
                                <label for="birthdate" class="form-label">Date of Birth:</label>
                                <input type="date" name="birthdate" class="form-control" id="birthdate" required>
                            </div>
                            <div class="mb-3">
                                <label for="blood_group" class="form-label">Blood Group:</label>
                                <select name="blood_group" id="blood_group" class="form-select" required>
                                    <option value="" disabled selected>Select your blood group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address:</label>
                                <textarea name="address" class="form-control" id="address" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Health Conditions:</label>
                                <div>
                                    <input type="checkbox" name="high_bp" id="high_bp" value="1">
                                    <label for="high_bp">High Blood Pressure</label>
                                </div>
                                <div>
                                    <input type="checkbox" name="diabetes" id="diabetes" value="1">
                                    <label for="diabetes">Diabetes</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender:</label>
                                <select name="gender" id="gender" class="form-select" required>
                                    <option value="" disabled selected>Select your gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-3">Sign Up</button>
                        </form>
                        <div class="text-center mt-3">
                            <span>Already have an account? <a href="login.php" class="text-primary">Login</a></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>

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


<!-- original code ends -->
<?php
session_start();
include 'include/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Form validation
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($role)) {
        $errors[] = 'Role is required.';
    } elseif (!in_array($role, ['patient', 'doctor', 'admin'])) {
        $errors[] = 'Invalid role selected.';
    }

    // If no errors, proceed with login
    if (empty($errors)) {
        $query = "SELECT * FROM $role WHERE email = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['role'] = $role;

                switch ($role) {
                    case 'patient':
                        $_SESSION['patient_id'] = $user['patient_id'];
                        header('Location: ./patient/dashboard.php');
                        exit();
                    case 'doctor':
                        $_SESSION['doctor_id'] = $user['doctor_id'];
                        header('Location: ./doctor/dashboard.php');
                        exit();
                    case 'admin':
                        $_SESSION['admin_id'] = $user['admin_id'];
                        header('Location: ./admin/dashboard.php');
                        exit();
                }
            } else {
                $errors[] = 'Incorrect email or password.';
            }
        } else {
            $errors[] = 'Email not found.';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Doctor Appointment System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            background: radial-gradient(circle, #0f0c29, #302b63, #24243e);
            color: #fff;
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: #2c2c2c;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            color: #7D7AFF;
            text-align: center;
        }

        .form-control {
            background-color: #444;
            border: none;
            color: #fff;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .form-control:focus {
            outline: none;
            border-color: #7D7AFF;
        }

        .btn-primary {
            background: linear-gradient(90deg, #7D7AFF, #5B5FEF);
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
            border-radius: 5px;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #5B5FEF, #7D7AFF);
        }

        .alert {
            background-color: #ff4d4d;
            color: white;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
        }

        .form-check-label {
            color: #ddd;
        }

        .form-check-input {
            border: 1px solid #7D7AFF;
        }

        @media (max-width: 768px) {
            .login-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>Login</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="show_password">
                <label class="form-check-label" for="show_password">Show Password</label>
            </div>

            <div class="mb-3">
                <select name="role" class="form-control" required>
                    <option value="" disabled selected>Select Role</option>
                    <option value="patient">Patient</option>
                    <option value="doctor">Doctor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>

            <div class="mt-3 text-center">
                <a href="forgot_password.php" class="text-light">Forgot Password?</a><br>
                <a href="signup.php" class="text-light">Don't have an account? Sign Up</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('show_password').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
        });
    </script>
</body>

</html>
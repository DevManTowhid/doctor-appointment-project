<?php
session_start();
include('../include/db.php');

// Check if the doctor is logged in
if (!isset($_SESSION['doctor_id']) || $_SESSION['role'] !== 'doctor') {
    header('Location: ../login.php');
    exit();
}

$doctor_id = $_SESSION['doctor_id'];

// Get doctor's name from the database
$query = "SELECT name FROM doctor WHERE doctor_id = '$doctor_id'";
$result = mysqli_query($conn, $query);
$doctor = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="dash_styling_doctor.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .dashboard-card {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php

    include("dash_design_doctor.php");

    ?>
    <div class="container">
        <!-- Welcome Doctor Section -->
        <div class="card dashboard-card">
            <div class="card-header bg-primary text-white">
                <h3>Welcome, Dr. <?php echo htmlspecialchars($doctor['name']); ?></h3>
            </div>
            <div class="card-body">
                <p>We're glad to have you on board!</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>

</html>
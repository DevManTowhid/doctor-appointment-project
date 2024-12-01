<?php
require('./include/db.php');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare - Doctor Appointment Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</head>

<body style="font-family: 'Roboto', sans-serif;">

    <header class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a href="index.php" class="navbar-brand fw-bold">MediCare</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="login.php" class="nav-link text-white">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="signup.php" class="nav-link btn btn-warning text-dark ms-2">Sign Up</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <div class="container-fluid bg-primary text-white text-center py-5 animate__animated animate__fadeIn">
        <h1 class="display-4 fw-bold">Welcome to MediCare</h1>
        <p class="lead">Effortless and reliable doctor appointment management at your fingertips.</p>
        <a href="doctors.php" class="btn btn-warning btn-lg mt-3 animate__animated animate__pulse animate__infinite">Our Doctors</a>
    </div>

    <!-- Features Section -->
    <div class="container py-5">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Easy Booking</h5>
                        <p class="card-text">Find the right doctor and book appointments instantly with just a few clicks.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Appointment Tracking</h5>
                        <p class="card-text">View all upcoming and past appointments in one place.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeInUp animate__delay-3s">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Anywhere Access</h5>
                        <p class="card-text">Access your appointments and medical records from any device, anywhere.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- About Us Section -->
    <div class="container text-center my-5 py-5 animate__animated animate__fadeIn animate__delay-4s">
        <h2 class="fw-bold">About MediCare</h2>
        <p class="text-muted">We aim to simplify healthcare access for everyone. With DocManage, you can easily book, track, and manage appointments, ensuring a smooth healthcare experience for patients and doctors alike.</p>
        <a href="about.php" class="btn btn-outline-primary btn-lg mt-3">Learn More</a>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-center text-white py-3">
        <div class="container">
            <p class="mb-0">&copy; 2024 MediCare. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
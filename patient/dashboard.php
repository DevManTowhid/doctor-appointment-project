<?php
session_start();

include '../include/db.php';

// Ensure the patient is logged in
if (!isset($_SESSION['patient_id']) || ($_SESSION['role'] !== 'patient')) {
    header("Location: ../login.php");
    exit;
}

// Get today's date in 'Y-m-d' format
$today = date('Y-m-d');
$patient_id = $_SESSION['patient_id']; // Assuming the patient ID is stored in the session

// Query to get today's total appointment count for the patient
$queryTodayCount = "SELECT COUNT(*) as today_count FROM appointment WHERE DATE(date) = ? AND patient_id = ?";
$stmt = $conn->prepare($queryTodayCount);
$stmt->bind_param("si", $today, $patient_id);
$stmt->execute();
$resultTodayCount = $stmt->get_result();
$todayCount = $resultTodayCount->fetch_assoc()['today_count'];

// Query to get distinct doctors count for the patient today
$queryDoctorCount = "SELECT COUNT(DISTINCT doctor_id) as doctor_count FROM appointment WHERE DATE(date) = ? AND patient_id = ?";
$stmt = $conn->prepare($queryDoctorCount);
$stmt->bind_param("si", $today, $patient_id);
$stmt->execute();
$resultDoctorCount = $stmt->get_result();
$doctorCount = $resultDoctorCount->fetch_assoc()['doctor_count'];

$patient_id = $_SESSION['patient_id'];

// Fetch the patient's name from the database
$queryPatientName = "SELECT name FROM patient WHERE patient_id = ?";
$stmt = $conn->prepare($queryPatientName);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$resultPatientName = $stmt->get_result();

if ($resultPatientName->num_rows > 0) {
    $patient_name = $resultPatientName->fetch_assoc()['name'];
} else {
    $patient_name = "Unknown Patient";
}
$stmt->close();
$conn->close();
?>
<?php include('dash_design.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="dash_styling.css">
    <style>

    </style>
</head>


<body>


    <main>
        <div class="container-fluid">
            <h2>Welcome, <?php echo htmlspecialchars($patient_name); ?>!</h2>
            <p>Here's an overview of your appointments for today:</p>

            <!-- Display today's total appointments -->
            <div class="alert alert-primary mt-4">
                <strong>Your Total Appointments Today:</strong> <?php echo $todayCount; ?>
            </div>

            <!-- Display distinct doctors count -->
            <div class="alert alert-secondary mt-4">
                <strong>Doctors You Have Appointments With:</strong> <?php echo $doctorCount; ?>
            </div>

            <!-- Buttons to navigate to other pages -->
            <div class="mt-5">
                <a href="view_appointments.php" class="btn btn-success">View All Appointments</a>
                <a href="view_doctors.php" class="btn btn-primary">Browse Doctors</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Doctor Appointment System. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
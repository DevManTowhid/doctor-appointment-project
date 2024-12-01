<?php
session_start();
include '../include/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get today's date in 'Y-m-d' format
$today = date('Y-m-d');

// Query to get today's total appointment count
$queryTodayCount = "SELECT COUNT(*) as today_count FROM appointment WHERE DATE(date) = ?";
$stmt = $conn->prepare($queryTodayCount);
$stmt->bind_param("s", $today);
$stmt->execute();
$resultTodayCount = $stmt->get_result();
$todayCount = $resultTodayCount->fetch_assoc()['today_count'];

// Query to get the count of appointments for each doctor today
$queryDoctorAppointments = "SELECT doctor_id, COUNT(*) as count FROM appointment WHERE DATE(date) = ? GROUP BY doctor_id";
$stmt = $conn->prepare($queryDoctorAppointments);
$stmt->bind_param("s", $today);
$stmt->execute();
$resultDoctorAppointments = $stmt->get_result();

$doctorAppointments = [];
while ($row = $resultDoctorAppointments->fetch_assoc()) {
    $doctorAppointments[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="dash_styling.css">
</head>

<body>
    <?php include('dash_design.php'); ?>

    <main>
        <div class="container-fluid">
            <h2>Welcome, Admin!</h2>
            <p>Select an option from the sidebar to manage the system.</p>

            <!-- Display today's total appointments -->
            <div class="alert alert-primary mt-4">
                <strong>Total Appointments Today:</strong> <?php echo $todayCount; ?>
            </div>

            <!-- Bar and Pie charts for appointment data -->
            <div class="row mt-5">
                <div class="col-md-6">
                    <canvas id="appointmentsBarChart"></canvas>
                </div>
                <div class="col-md-6">
                    <canvas id="appointmentsPieChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Doctor Appointment System. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS and Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Pass PHP data to JavaScript
        const doctorAppointments = <?php echo json_encode($doctorAppointments); ?>;

        // Prepare data for the bar and pie charts
        const doctorIds = doctorAppointments.map(app => 'Doctor ' + app.doctor_id);
        const appointmentCounts = doctorAppointments.map(app => app.count);

        // Bar Chart for Appointments Count by Doctor
        const barCtx = document.getElementById('appointmentsBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: doctorIds,
                datasets: [{
                    label: 'Number of Appointments Today',
                    data: appointmentCounts,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Pie Chart for Appointments Percentage by Doctor
        const pieCtx = document.getElementById('appointmentsPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: doctorIds,
                datasets: [{
                    data: appointmentCounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const count = context.raw;
                                const total = appointmentCounts.reduce((a, b) => a + b, 0);
                                const percentage = ((count / total) * 100).toFixed(2) + '%';
                                return `${label}: ${count} (${percentage})`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>
<?php
session_start();
include '../include/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['doctor_id']) || $_SESSION['role'] !== 'doctor') {
    header('Location: ../login.php');
    exit();
}

// Fetch all specialties for the dropdown filter
$specialtyQuery = "SELECT * FROM specialization";
$specialtyResult = mysqli_query($conn, $specialtyQuery);

// Get selected specialty filter from GET request
$selectedSpecialty = isset($_GET['specialty']) ? $_GET['specialty'] : '';

// Fetch doctors based on selected specialty
$query = "
    SELECT doctor.*, specialization.category 
    FROM doctor
    LEFT JOIN specialization ON doctor.spec_id = specialization.spec_id
";
if ($selectedSpecialty) {
    $query .= " WHERE doctor.spec_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $selectedSpecialty);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conn, $query);
}

// Fetch specialty vs doctor count for the pie chart
$chartDataQuery = "
    SELECT specialization.category AS specialty, COUNT(doctor.doctor_id) AS doctor_count
    FROM specialization
    LEFT JOIN doctor ON specialization.spec_id = doctor.spec_id
    GROUP BY specialization.category
";
$chartDataResult = mysqli_query($conn, $chartDataQuery);

// Prepare data for the chart
$specialties = [];
$doctorCounts = [];
while ($row = mysqli_fetch_assoc($chartDataResult)) {
    $specialties[] = $row['specialty'];
    $doctorCounts[] = $row['doctor_count'];
}

// Fetch total number of doctors
$totalDoctorsQuery = "SELECT COUNT(*) AS total_doctors FROM doctor";
$totalDoctorsResult = mysqli_query($conn, $totalDoctorsQuery);
$totalDoctors = mysqli_fetch_assoc($totalDoctorsResult)['total_doctors'];

// Fetch total number of specialties
$totalSpecialtiesQuery = "SELECT COUNT(*) AS total_specialties FROM specialization";
$totalSpecialtiesResult = mysqli_query($conn, $totalSpecialtiesQuery);
$totalSpecialties = mysqli_fetch_assoc($totalSpecialtiesResult)['total_specialties'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Doctors</title>
    <link rel="stylesheet" href="dash_styling.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        .summary-card {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
            padding: 20px;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }

        .summary-card-header h3 {
            font-size: 22px;
            margin: 0;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .summary-card-body {
            margin-top: 10px;
        }

        .summary-number {
            font-size: 36px;
            font-weight: 800;
            margin: 0;
            color: #f9f9f9;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.4);
        }

        body {
            background-color: #f7f9fb;
            font-family: 'Roboto', sans-serif;
        }

        .container-fluid {
            max-width: 900px;
            margin-top: 40px;
        }

        h2 {
            color: #34495e;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }

        .filter-form {
            display: flex;
            justify-content: center;
            margin-bottom: 25px;
        }

        .filter-form select {
            width: 250px;
            padding: 8px 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f3f3f3;
            transition: background 0.3s ease;
        }

        .filter-form select:hover {
            background-color: #e9ecef;
        }

        .doctor-card {
            margin-bottom: 25px;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
        }

        .doctor-card-header {
            background: linear-gradient(135deg, #5a9fd6, #34495e);
            color: #fff;
            padding: 15px 20px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            font-size: 20px;
            font-weight: 500;
        }

        .doctor-card-body {
            padding: 20px;
            color: #2c3e50;
        }

        .doctor-card-body p {
            font-size: 16px;
            margin: 5px 0;
        }

        footer {
            text-align: center;
            padding: 15px;
            margin-top: 40px;
            background-color: #f8f9fa;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <?php include('dash_design_doctor.php'); ?>
    <!-- Summary Section -->
    <div class="row mt-4">
        <!-- Total Doctors -->
        <div class="col-md-6">
            <div class="summary-card">
                <div class="summary-card-header">
                    <h3>Total Doctors</h3>
                </div>
                <div class="summary-card-body">
                    <p class="summary-number"><?php echo htmlspecialchars($totalDoctors); ?></p>
                </div>
            </div>
        </div>

        <!-- Total Specialties -->
        <div class="col-md-6">
            <div class="summary-card">
                <div class="summary-card-header">
                    <h3>Total Specialties</h3>
                </div>
                <div class="summary-card-body">
                    <p class="summary-number"><?php echo htmlspecialchars($totalSpecialties); ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <h2>Specialty vs Number of Doctors</h2>
        <canvas id="specialtyPieChart" width="400" height="400"></canvas>
    </div>
    <main>
        <div class="container-fluid">
            <!-- Specialty Filter Dropdown -->
            <form method="GET" action="view_doctors.php" class="filter-form">
                <select id="specialty" name="specialty" onchange="this.form.submit()">
                    <option value="">All Specialties</option>
                    <?php
                    while ($specialty = mysqli_fetch_assoc($specialtyResult)) {
                        $selected = $selectedSpecialty == $specialty['spec_id'] ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($specialty['spec_id']) . "' $selected>" . htmlspecialchars($specialty['category']) . "</option>";
                    }
                    ?>
                </select>
            </form>
            <!-- Details Button to Show/Hide Doctor List -->

            <!-- Doctors List and Chart Side-by-Side -->
            <details>
                <summary>Doctors List</summary>
                <p>
                <div class="row" id="doctorsList">
                    <!-- Doctors List -->
                    <div class="col-md-6">
                        <h2>Doctors List</h2>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($doctor = mysqli_fetch_assoc($result)): ?>
                                <div class="doctor-card">
                                    <div class="doctor-card-header">
                                        Dr. <?php echo htmlspecialchars($doctor['name']); ?> - <?php echo htmlspecialchars($doctor['category']); ?>
                                    </div>
                                    <div class="doctor-card-body">
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($doctor['phone']); ?></p>
                                        <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($doctor['blood_group']); ?></p>
                                        <p><strong>Gender:</strong> <?php echo htmlspecialchars($doctor['gender']); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-center text-muted">No doctors found for the selected specialty.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Pie Chart -->

                </div>
                </p>
            </details>

        </div>

    </main>

    <footer>
        <p>&copy; 2024 Doctor Appointment System. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('specialtyPieChart').getContext('2d');

            // Data from PHP
            const specialties = <?php echo json_encode($specialties); ?>;
            const doctorCounts = <?php echo json_encode($doctorCounts); ?>;

            // Create the chart
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: specialties,
                    datasets: [{
                        label: 'Number of Doctors',
                        data: doctorCounts,
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF',
                            '#FF9F40'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return `${tooltipItem.label}: ${tooltipItem.raw} doctors`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

    <style>
        details>p {
            background: white;
            padding: 25px;
            margin: 12px;
        }
    </style>

</body>

</html>
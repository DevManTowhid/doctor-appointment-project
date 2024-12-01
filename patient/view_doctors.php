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


</head>


<?php include('dash_design.php'); ?>

<body class="col">
    <style>
        body {
            background-color: #f7f9fb;
            font-family: 'Roboto', sans-serif;
            color: #34495e;
        }

        h2 {
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            text-transform: uppercase;
        }

        .container-fluid {
            max-width: 1000px;
            margin-top: 40px;
        }

        .filter-form {
            display: flex;
            justify-content: center;
            margin-bottom: 25px;
        }

        .filter-form select {
            width: 300px;
            padding: 10px 15px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            transition: border-color 0.3s ease;
        }

        .filter-form select:focus {
            border-color: #2575fc;
            outline: none;
        }

        .doctor-card {
            margin-bottom: 25px;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .doctor-card-header {
            background: linear-gradient(135deg, #5a9fd6, #34495e);
            color: #fff;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            font-size: 20px;
            font-weight: 600;
        }

        .doctor-card-body {
            padding: 15px;
            color: #2c3e50;
        }

        .summary-card {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }

        .summary-number {
            font-size: 36px;
            font-weight: 800;
        }

        details {
            background: #fff;
            border-radius: 5px;
            margin-top: 20px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        details summary {
            font-weight: 600;
            font-size: 18px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        footer {
            text-align: center;
            padding: 15px;
            margin-top: 40px;
            background-color: #f8f9fa;
            color: #6c757d;
            font-size: 14px;
        }

        @media (min-width: 768px) {

            .doctor-card,
            .summary-card {
                max-width: 48%;
                margin: 10px 1%;
                display: inline-block;
            }
        }
    </style>
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
                                        <p><strong>
                                                <a href="book_appointment.php?doctor_id=<?php echo htmlspecialchars($doctor['doctor_id']); ?>&spec_id=<?php echo urlencode($doctor['spec_id']); ?>" class="btn btn-primary">
                                                    Book Appointment
                                                </a>
                                            </strong></p>
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
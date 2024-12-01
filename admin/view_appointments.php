<?php
session_start();
include '../include/db.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== "admin") {
    header('Location: ../login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$today = date('Y-m-d');

// Initialize filter variables
$patient_filter = $_GET['patient'] ?? '';
$date_filter = $_GET['appointment_date'] ?? '';
$specialty_filter = $_GET['specialty'] ?? '';
$doctor_filter = $_GET['doctor'] ?? '';

// Fetch specialties
$specialties_result = mysqli_query($conn, "SELECT spec_id, category FROM specialization ORDER BY category ASC");

// Fetch doctors
$doctors_result = mysqli_query($conn, "SELECT doctor_id, name, spec_id FROM doctor ORDER BY name ASC");
$doctors = [];
while ($doctor = mysqli_fetch_assoc($doctors_result)) {
    $doctors[$doctor['spec_id']][] = $doctor; // Group doctors by specialty
}

// Only process the query if at least one filter is applied
$show_results = !empty($date_filter) || !empty($patient_filter) || !empty($specialty_filter) || !empty($doctor_filter);
if ($show_results) {
    // Base query
    $query = "SELECT 
                a.appointment_id, 
                patient.name AS patient_name, 
                doctor.name AS doctor_name, 
                specialization.category AS specialty_name, 
                a.date AS appointment_date, 
                t.time_start, 
                t.time_end, 
                a.status
              FROM appointment a
              LEFT JOIN doctor ON a.doctor_id = doctor.doctor_id
              LEFT JOIN patient ON a.patient_id = patient.patient_id
              LEFT JOIN specialization ON doctor.spec_id = specialization.spec_id
              LEFT JOIN timeslots t ON a.timeslot_id = t.timeslot_id
              WHERE 1=1";

    // Apply filters
    $params = [];
    $types = '';
    if ($patient_filter !== '') {
        $query .= " AND patient.name LIKE ?";
        $params[] = "%$patient_filter%";
        $types .= "s";
    }
    if ($date_filter !== '') {
        $query .= " AND DATE(a.date) = ?";
        $params[] = $date_filter;
        $types .= "s";
    }
    if ($specialty_filter !== '') {
        $query .= " AND doctor.spec_id = ?";
        $params[] = $specialty_filter;
        $types .= "i";
    }
    if ($doctor_filter !== '') {
        $query .= " AND doctor.doctor_id = ?";
        $params[] = $doctor_filter;
        $types .= "i";
    }

    // Sort results
    $query .= " ORDER BY a.date DESC";

    // Execute query
    $stmt = mysqli_prepare($conn, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}

// Fetch patients for dropdown
$patients_result = mysqli_query($conn, "SELECT patient_id, name FROM patient ORDER BY name ASC");

// Handle marking appointment as done
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['done_appointment_id'])) {
    $appointment_id = $_POST['done_appointment_id'];
    $done_query = "UPDATE appointment SET status = 'done' WHERE appointment_id = ?";
    $stmt = mysqli_prepare($conn, $done_query);
    mysqli_stmt_bind_param($stmt, 'i', $appointment_id);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: view_appointments.php?" . http_build_query($_GET) . "&status=done");
        exit();
    } else {
        echo "Error updating appointment status: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}
?>

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointments</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="dash_styling.css">
    <style>
        th {
            color: black;
            font-weight: 500;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
        }

        form {
            width: 75%;
        }
    </style>
</head>

<body>
    <?php include("dash_design.php"); ?>

    <div class="bg:white">
        <header>
            <h1>View Appointments</h1>
        </header>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="mb-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-sm-12">
                    <label for="specialty" class="form-label">Specialty</label>
                    <select class="form-select" id="specialty" name="specialty">
                        <option value="">Show All</option>
                        <?php while ($specialty = mysqli_fetch_assoc($specialties_result)): ?>
                            <option value="<?= $specialty['spec_id']; ?>" <?= $specialty['spec_id'] == $specialty_filter ? 'selected' : ''; ?>>
                                <?= $specialty['category']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6 col-sm-12">
                    <label for="doctor" class="form-label">Doctor</label>
                    <select class="form-select" id="doctor" name="doctor" disabled>
                        <option value="">Select Specialty</option>
                        <?php foreach ($doctors as $spec_id => $doctor_list): ?>
                            <?php foreach ($doctor_list as $doctor): ?>
                                <option
                                    value="<?= $doctor['doctor_id']; ?>"
                                    data-spec-id="<?= $spec_id; ?>"
                                    <?= $doctor['doctor_id'] == $doctor_filter ? 'selected' : ''; ?>>
                                    <?= $doctor['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
                </div>


                <div class="col-md-6 col-sm-12">
                    <label for="patient" class="form-label">Patient</label>
                    <select class="form-select" id="patient" name="patient">
                        <option value="">Show All</option>
                        <?php while ($patient = mysqli_fetch_assoc($patients_result)): ?>
                            <option value="<?= $patient['name']; ?>" <?= $patient['name'] == $patient_filter ? 'selected' : ''; ?>>
                                <?= $patient['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6 col-sm-12">
                    <label for="appointment_date" class="form-label">Appointment Date</label>
                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" value="<?= $date_filter; ?>">
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit"><i class="fas fa-filter"></i> Apply Filters</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <?php if ($show_results): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Specialty</th>
                        <th>Appointment Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($appointment = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($appointment['appointment_id']); ?></td>
                                <td><?= htmlspecialchars($appointment['patient_name']); ?></td>
                                <td><?= htmlspecialchars($appointment['doctor_name']); ?></td>
                                <td><?= htmlspecialchars($appointment['specialty_name']); ?></td>
                                <td><?= htmlspecialchars(date("Y-m-d", strtotime($appointment['appointment_date']))); ?></td>
                                <td><?= htmlspecialchars(date("H:i", strtotime($appointment['time_start']))); ?></td>
                                <td><?= htmlspecialchars(date("H:i", strtotime($appointment['time_end']))); ?></td>
                                <td><?= htmlspecialchars($appointment['status']); ?></td>
                                <td>
                                    <?php if ($appointment['status'] !== 'done'): ?>
                                        <form method="POST" onsubmit="return confirm('Mark as done?');">
                                            <input type="hidden" name="done_appointment_id" value="<?= $appointment['appointment_id']; ?>">
                                            <button type="submit" class="btn btn-success">Mark as Done</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No appointments found for the applied filters.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">Please select at least one filter to view appointments.</p>
        <?php endif; ?>
    </div>


    <script>
        const specialtyDropdown = document.getElementById('specialty');
        const doctorDropdown = document.getElementById('doctor');

        // Disable doctor dropdown by default
        doctorDropdown.disabled = true;

        // Event listener for specialty dropdown
        specialtyDropdown.addEventListener('change', function() {
            const specialtyId = this.value;

            // Enable doctor dropdown if a specialty is selected
            doctorDropdown.disabled = specialtyId === '';

            // Filter doctors based on selected specialty
            Array.from(doctorDropdown.options).forEach(option => {
                const specId = option.getAttribute('data-spec-id');
                option.style.display = specId === specialtyId || specialtyId === '' ? '' : 'none';
            });

            // Reset doctor selection if it's not valid for the selected specialty
            if (doctorDropdown.value && doctorDropdown.selectedOptions[0].style.display === 'none') {
                doctorDropdown.value = '';
            }
        });
    </script>


</body>

</html>
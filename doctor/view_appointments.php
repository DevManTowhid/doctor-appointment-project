<?php
session_start();
include '../include/db.php';

// Check if the user is logged in as a doctor
if (!isset($_SESSION['doctor_id']) || $_SESSION['role'] !== 'doctor') {
    header('Location: ../login.php');
    exit();
}

$doctor_id = $_SESSION['doctor_id'];
$today = date('Y-m-d');

// Fetch filter values from URL parameters
$patient_filter = isset($_GET['patient']) ? $_GET['patient'] : '';
$date_filter = isset($_GET['appointment_date']) ? $_GET['appointment_date'] : '';

$query = "SELECT 
            appointment.appointment_id, 
            patient.name AS patient_name, 
            doctor.name AS doctor_name, 
            specialization.category AS specialty_name, 
            appointment.date AS appointment_date, 
            timeslots.time_start, 
            timeslots.time_end, 
            appointment.status
          FROM appointment
          LEFT JOIN doctor ON appointment.doctor_id = doctor.doctor_id
          LEFT JOIN patient ON appointment.patient_id = patient.patient_id
          LEFT JOIN specialization ON doctor.spec_id = specialization.spec_id
          LEFT JOIN timeslots ON appointment.timeslot_id = timeslots.timeslot_id
          WHERE appointment.doctor_id = '$doctor_id'";

if ($patient_filter !== '') {
    $query .= " AND patient.name LIKE '%$patient_filter%'";
}
if ($date_filter !== '') {
    $query .= " AND DATE(appointment.date) = '$date_filter'";
}

$query .= " ORDER BY appointment.date DESC";
$result = mysqli_query($conn, $query);
$patients_result = mysqli_query($conn, "
    SELECT DISTINCT p.patient_id, p.name
    FROM appointment a
    JOIN patient p ON a.patient_id = p.patient_id
    WHERE a.doctor_id = '$doctor_id'");

// Handle marking appointment as done
if (isset($_POST['done_appointment_id'])) {
    $appointment_id = $_POST['done_appointment_id'];
    $done_query = "UPDATE appointment SET status = 'done' WHERE appointment_id = ?";
    if ($stmt = mysqli_prepare($conn, $done_query)) {
        mysqli_stmt_bind_param($stmt, 'i', $appointment_id);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: view_appointments.php?" . http_build_query($_GET) . "&status=done");
            exit();
        } else {
            echo "Error updating appointment status: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointments</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="dash_styling_doctor.css">
    <style>
        th {
            color: black;
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

    <?php include("dash_design_doctor.php"); ?>

    <header>
        <h1>View Appointments</h1>
    </header>

    <h2>Appointments</h2>

    <!-- Filter Form -->
    <form method="GET" class="mb-4">
        <div class="container">
            <div class="row g-4">



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

    <?php if (mysqli_num_rows($result) == 0): ?>
        <div class="alert alert-warning" role="alert">
            No appointments found.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Appointment Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($appointment = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['appointment_id']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['specialty_name']); ?></td>
                            <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($appointment['appointment_date']))); ?></td>
                            <td><?php echo htmlspecialchars($appointment['time_start']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['time_end']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                            <td>
                                <!-- Mark Appointment as Done Button -->
                                <?php
                                $appointment_datetime = new DateTime($appointment['appointment_date'] . ' ' . $appointment['time_start']);
                                $current_time = new DateTime();

                                if ($appointment['status'] !== 'done') {
                                    if ($current_time > $appointment_datetime): ?>
                                        <form method="POST" action="view_appointments.php?<?php echo http_build_query($_GET); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to mark this appointment as done?');">
                                            <input type="hidden" name="done_appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                            <button type="submit" class="btn btn-success mt-3 w-75 p-2">Mark as Done</button>
                                        </form>
                                    <?php else: ?>
                                        <p class="text-secondary">Cannot mark as done until appointment time has passed.</p>
                                <?php endif;
                                } ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</body>

</html>
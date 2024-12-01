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

$patient_id = $_SESSION['patient_id'];

// Check if appointment ID is provided
if (!isset($_GET['appointment_id'])) {
    header("Location: view_appointments.php?error=missing_id");
    exit;
}

$appointment_id = $_GET['appointment_id'];

// Fetch appointment details
$query = "
    SELECT 
        a.appointment_id, 
        a.date, 
        a.timeslot_id, 
        a.doctor_id, 
        d.name AS doctor_name, 
        s.category AS specialty
    FROM appointment a
    JOIN doctor d ON a.doctor_id = d.doctor_id
    JOIN specialization s ON d.spec_id = s.spec_id
    WHERE a.appointment_id = ? AND a.patient_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $appointment_id, $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: view_appointments.php?error=not_found");
    exit;
}

$appointment = $result->fetch_assoc();
// Fetch available time slots for the doctor on the given date
$timeslot_query = "
    SELECT DISTINCT t.timeslot_id, t.time_start, t.time_end
    FROM timeslots t
    WHERE t.timeslot_id NOT IN (
        SELECT DISTINCT a.timeslot_id
        FROM appointment a
        WHERE a.date = ? 
            AND (a.doctor_id = ? OR a.patient_id = ?)
            AND a.appointment_id != ?)";  // Exclude the current appointment's timeslot
$timeslot_stmt = $conn->prepare($timeslot_query);
$timeslot_stmt->bind_param('ssss', $appointment['date'], $appointment['doctor_id'], $patient_id, $appointment_id);
$timeslot_stmt->execute();
$timeslot_result = $timeslot_stmt->get_result();
$timeslots = $timeslot_result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment</title>
    <link rel="stylesheet" href="dash_styling_patient.css">
    <style>
        .form-group {
            margin: 20px 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .btn-edit {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-edit:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <?php include("dash_design.php"); ?>

    <h1>Edit Appointment</h1>

    <form action="process_edit.php" method="POST">
        <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">

        <div class="form-group">
            <label for="doctor_name">Doctor</label>
            <input type="text" id="doctor_name" value="<?php echo htmlspecialchars($appointment['doctor_name']); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="specialty">Specialty</label>
            <input type="text" id="specialty" value="<?php echo htmlspecialchars($appointment['specialty']); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="date">Appointment Date</label>
            <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($appointment['date']); ?>" required>
        </div>

        <div class="form-group">
            <label for="timeslot">Select Timeslot</label>
            <select name="timeslot_id" id="timeslot" required>
                <option value="">-- Select Timeslot --</option>
                <?php foreach ($timeslots as $timeslot): ?>
                    <option value="<?php echo htmlspecialchars($timeslot['timeslot_id']); ?>"
                        <?php echo ($timeslot['timeslot_id'] == (string) $appointment['timeslot_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($timeslot['time_start']) . ' - ' . htmlspecialchars($timeslot['time_end']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>





        <button type="submit" class="btn-edit">Update Appointment</button>
    </form>
</body>

</html>
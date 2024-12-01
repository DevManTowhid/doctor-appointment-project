<?php
// Set the default time zone to Dhaka (Bangladesh)
date_default_timezone_set('Asia/Dhaka');
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("../include/db.php");

// Ensure the patient is logged in
if (!isset($_SESSION['patient_id']) || ($_SESSION['role'] !== 'patient')) {
    header("Location: ../login.php");
    exit;
}

// Fetch patient details
$patient_id = $_SESSION['patient_id'];
$stmt = $conn->prepare("SELECT name FROM patient WHERE patient_id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient_name = ($result->num_rows > 0) ? $result->fetch_assoc()['name'] : "Unknown Patient";

// Fetch specializations
$specializations = [];
$result = $conn->query("SELECT spec_id, category FROM specialization");
if ($result) {
    $specializations = $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch doctors based on selected specialization
$doctors = [];
if (isset($_GET['spec_id'])) {
    $spec_id = $_GET['spec_id'];
    $stmt = $conn->prepare("SELECT doctor_id, name FROM doctor WHERE spec_id = ?");
    $stmt->bind_param("i", $spec_id);
    $stmt->execute();
    $doctors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Fetch available timeslots based on selected doctor and date
$timeslots = [];
if (isset($_GET['doctor_id']) && isset($_GET['date'])) {
    $doctor_id = $_GET['doctor_id'];
    $date = $_GET['date'];

    $stmt = $conn->prepare("
        SELECT t.timeslot_id, t.time_start, t.time_end
        FROM timeslots t
        WHERE t.timeslot_id NOT IN (
            SELECT DISTINCT a.timeslot_id
            FROM appointment a
            WHERE a.date = ? AND status = 'booked'
              AND (a.doctor_id = ? OR a.patient_id = ?)
        )
    ");
    $stmt->bind_param("sii", $date, $doctor_id, $patient_id);
    $stmt->execute();
    $timeslots = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = $_POST['date'];
    $timeslot_id = $_POST['timeslot_id'];
    $doctor_id = $_POST['doctor'];
    $spec_id = $_POST['specialty'];

    $stmt = $conn->prepare("
        SELECT * FROM appointment 
        WHERE (patient_id = ? OR doctor_id = ?) 
        AND date = ? 
        AND timeslot_id = ? 
        AND status = 'booked'
    ");
    $stmt->bind_param("iisi", $patient_id, $doctor_id, $appointment_date, $timeslot_id);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
        $message = "You already have an appointment at this time.";
    } else {
        $status = "booked"; // Assign the string to a variable
        $current_datetime = date("Y-m-d H:i:s");

        $stmt = $conn->prepare("
            INSERT INTO appointment (patient_id, doctor_id, date, timeslot_id, spec_id, status, booking_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisiiss", $patient_id, $doctor_id, $appointment_date, $timeslot_id, $spec_id, $status, $current_datetime);

        $message = $stmt->execute() ? "Appointment booked successfully!" : "Failed to book appointment.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <style>
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        select,
        button {
            margin-bottom: 15px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <?php include("dash_design.php"); ?>
    <div class="container">
        <h1>Book Appointment</h1>
        <?php if (isset($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="patient_name">Patient Name:</label>
            <input type="text" id="patient_name" value="<?= htmlspecialchars($patient_name) ?>" readonly>

            <label for="specialty">Specialty:</label>
            <select id="specialty" name="specialty" onchange="window.location.href='book_appointment.php?spec_id=' + this.value;">
                <option value="">Select Specialty</option>
                <?php foreach ($specializations as $specialty): ?>
                    <option value="<?= $specialty['spec_id'] ?>" <?= isset($_GET['spec_id']) && $_GET['spec_id'] == $specialty['spec_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($specialty['category']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (isset($_GET['spec_id']) && count($doctors) > 0): ?>
                <label for="doctor">Doctor:</label>
                <select id="doctor" name="doctor" onchange="window.location.href='book_appointment.php?spec_id=<?= $_GET['spec_id'] ?>&doctor_id=' + this.value;">
                    <option value="">Select Doctor</option>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?= $doctor['doctor_id'] ?>" <?= isset($_GET['doctor_id']) && $_GET['doctor_id'] == $doctor['doctor_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($doctor['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <?php if (isset($_GET['doctor_id'])): ?>
                <label for="date">Date:</label>
                <input type="date" id="date" name="date" required
                    value="<?= isset($_GET['date']) ? $_GET['date'] : '' ?>"
                    min="<?= date('Y-m-d') ?>"
                    onchange="window.location.href='book_appointment.php?spec_id=<?= $_GET['spec_id'] ?>&doctor_id=<?= $_GET['doctor_id'] ?>&date=' + this.value;">
            <?php endif; ?>

            <?php if (isset($_GET['doctor_id']) && isset($_GET['date'])): ?>
                <label for="timeslot_id">Timeslot:</label>
                <select id="timeslot_id" name="timeslot_id" required>
                    <option value="">Select Timeslot</option>
                    <?php foreach ($timeslots as $timeslot): ?>
                        <option value="<?= $timeslot['timeslot_id'] ?>" <?= isset($_POST['timeslot_id']) && $_POST['timeslot_id'] == $timeslot['timeslot_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($timeslot['time_start']) ?> - <?= htmlspecialchars($timeslot['time_end']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <button type="submit">Book Appointment</button>
        </form>
    </div>
</body>

</html>
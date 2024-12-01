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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'];
    $new_date = $_POST['date'];
    $new_timeslot = $_POST['timeslot_id'];
    $patient_id = $_SESSION['patient_id'];

    if (empty($appointment_id) || empty($new_date) || empty($new_timeslot)) {
        header("Location: edit_appointment.php?error=missing_data&appointment_id=$appointment_id");
        exit;
    }

    // Update the appointment
    $query = "
        UPDATE appointment 
        SET date = ?, timeslot_id = ?
        WHERE appointment_id = ? AND patient_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssss', $new_date, $new_timeslot, $appointment_id, $patient_id);

    if ($stmt->execute()) {
        header("Location: view_appointments.php?message=edit_success");
    } else {
        header("Location: edit_appointment.php?error=update_failed&appointment_id=$appointment_id");
    }
    exit;
}

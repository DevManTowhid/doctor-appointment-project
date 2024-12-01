<?php
session_start();
if (!isset($_SESSION['patient_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
    exit();
}

include '../include/db.php';

if (!isset($_POST['appointment_id'])) {

    error_log("No appointment ID selected"); // Log the ID for debugging
}



if (isset($_POST['cancel'])) {
    $appointment_id = $_POST['appointment_id'];

    // Update the appointment's status to 'cancelled'
    $update_query = "UPDATE appointment SET status = 'cancelled' WHERE appointment_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('i', $appointment_id);
    $stmt->execute();
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<script>alert('Appointment cancelled successfully.');</script>";
            header('Location: dashboard.php');
            exit();
        } else {
            echo "<script>alert('No appointment was found with that ID.');</script>";
        }
    } else {
        echo "<script>alert('Error executing query: " . $stmt->error . "');</script>";
    }
}

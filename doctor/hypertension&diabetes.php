<?php
session_start();
include('../include/db.php');

// Check if the doctor is logged in
if (!isset($_SESSION['doctor_id']) || $_SESSION['role'] !== 'doctor') {
    header('Location: ../login.php');
    exit();
}
// Assuming the doctor's ID is stored in session or passed as a parameter
$doctor_id = $_SESSION['doctor_id']; // You can replace this with how you fetch the doctor's ID

// Fetch patients with hypertension who have booked at least one appointment with the current doctor
$sql_hypertension = "
    SELECT DISTINCT p.patient_id, p.name 
    FROM patient p
    INNER JOIN appointment a ON p.patient_id = a.patient_id
    WHERE p.high_bp = 1 AND a.doctor_id = '$doctor_id'";
$result_hypertension = $conn->query($sql_hypertension);

// Fetch patients with diabetes who have booked at least one appointment with the current doctor
$sql_diabetes = "
    SELECT DISTINCT p.patient_id, p.name 
    FROM patient p
    INNER JOIN appointment a ON p.patient_id = a.patient_id
    WHERE p.diabetes = 1 AND a.doctor_id = '$doctor_id'";
$result_diabetes = $conn->query($sql_diabetes);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Health Conditions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="dash_styling_doctor.css"> <!-- Link to your CSS file -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        h1,
        h2 {
            text-align: center;
            margin-top: 20px;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .empty-state {
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <!-- Include Dashboard Design -->
    <?php include("dash_design_doctor.php"); ?>

    <h1>Patients with Health Conditions</h1>

    <!-- Hypertension Patients Section -->
    <h2>Hypertension Patients</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_hypertension->num_rows > 0): ?>
                <?php while ($row = $result_hypertension->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['patient_id']); ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="empty-state">No hypertension patients found who have booked an appointment with you.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Diabetes Patients Section -->
    <h2>Diabetes Patients</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_diabetes->num_rows > 0): ?>
                <?php while ($row = $result_diabetes->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['patient_id']); ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" class="empty-state">No diabetes patients found who have booked an appointment with you.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
    // Close database connection
    $conn->close();
    ?>
</body>

</html>
<?php
session_start();
// Database connection
include("../include/db.php");

// Check if the doctor is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
// Fetch patients with hypertension
$sql_hypertension = "SELECT patient_id, name FROM patient WHERE high_bp = 1";
$result_hypertension = $conn->query($sql_hypertension);

// Fetch patients with diabetes
$sql_diabetes = "SELECT patient_id, name FROM patient WHERE diabetes = 1";
$result_diabetes = $conn->query($sql_diabetes);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Health Conditions</title>
    <link rel="stylesheet" href="dash_styling.css"> <!-- Link to your CSS file -->
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
    <?php include("dash_design.php"); ?>

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
                    <td colspan="2" class="empty-state">No patients with hypertension found.</td>
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
                    <td colspan="2" class="empty-state">No patients with diabetes found.</td>
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
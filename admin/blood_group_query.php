<?php
// Database connection
include("../include/db.php");

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve filter values from the form
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all'; // Default to 'all'
$blood_group_filter = isset($_GET['blood_group']) ? $_GET['blood_group'] : '';

// Prepare base SQL query
$sql = "SELECT id, name, phone, blood_group, role FROM (
            SELECT patient_id AS id, name, phone, blood_group, 'patient' AS role FROM patient
            UNION ALL
            SELECT doctor_id AS id, name, phone, blood_group, 'doctor' AS role FROM doctor
            UNION ALL
            SELECT admin_id AS id, name, phone, blood_group, 'admin' AS role FROM admin
        ) AS combined_table";

// Add filters dynamically
$conditions = [];
$params = [];
$types = '';

if ($role_filter !== 'all') {
    $conditions[] = "role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if ($blood_group_filter) {
    $conditions[] = "(blood_group = ? OR blood_group IS NULL)";
    $params[] = $blood_group_filter;
    $types .= "s";
}


if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Group Query</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dash_styling.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .filter-form {
            max-width: 600px;
            margin: 0 auto 20px;
            display: flex;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .empty-state {
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <?php include('dash_design.php'); ?>
    <h1>Blood Group Query</h1>

    <!-- Filter Form -->
    <form method="GET" class="filter-form">
        <select name="role" class="form-select">
            <option value="all" <?= $role_filter === 'all' ? 'selected' : ''; ?>>All Roles</option>
            <option value="admin" <?= $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
            <option value="doctor" <?= $role_filter === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
            <option value="patient" <?= $role_filter === 'patient' ? 'selected' : ''; ?>>Patient</option>
        </select>

        <select name="blood_group" class="form-select">
            <option value="">All Blood Groups</option>
            <option value="A+" <?= $blood_group_filter === 'A+' ? 'selected' : ''; ?>>A+</option>
            <option value="A-" <?= $blood_group_filter === 'A-' ? 'selected' : ''; ?>>A-</option>
            <option value="B+" <?= $blood_group_filter === 'B+' ? 'selected' : ''; ?>>B+</option>
            <option value="B-" <?= $blood_group_filter === 'B-' ? 'selected' : ''; ?>>B-</option>
            <option value="O+" <?= $blood_group_filter === 'O+' ? 'selected' : ''; ?>>O+</option>
            <option value="O-" <?= $blood_group_filter === 'O-' ? 'selected' : ''; ?>>O-</option>
            <option value="AB+" <?= $blood_group_filter === 'AB+' ? 'selected' : ''; ?>>AB+</option>
            <option value="AB-" <?= $blood_group_filter === 'AB-' ? 'selected' : ''; ?>>AB-</option>
        </select>

        <button type="submit"><i class="fas fa-filter"></i> Apply Filters</button>
    </form>

    <!-- Results Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Blood Group</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']); ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['phone']); ?></td>
                        <td><?= htmlspecialchars($row['blood_group'] ?? 'N/A'); ?></td>
                        <td><?= ucfirst(htmlspecialchars($row['role'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="empty-state">No records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
    // Close database connection
    $stmt->close();
    $conn->close();
    ?>
</body>

</html>
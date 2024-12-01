<?php
// edit_profile.php
session_start();
require '../include/db.php'; // Include your database connection

// Check if the doctor is logged in
if (!isset($_SESSION['doctor_id']) || ($_SESSION['role'] !== 'doctor')) {
    header("Location: ../login.php"); // Redirect to login if not logged in
    exit;
}
$doctor_id = $_SESSION['doctor_id'];

// Initialize filter variables
$blood_group_filter = isset($_GET['blood_group']) ? $_GET['blood_group'] : '';
$high_bp_filter = isset($_GET['high_bp']) ? $_GET['high_bp'] : '';
$diabetes_filter = isset($_GET['diabetes']) ? $_GET['diabetes'] : '';

// Construct the WHERE clause based on selected filters
$where_conditions = "a.doctor_id = ?";

if ($blood_group_filter) {
    $where_conditions .= " AND p.blood_group = ?";
}
if ($high_bp_filter !== '') {
    $where_conditions .= " AND p.high_bp = ?";
}
if ($diabetes_filter !== '') {
    $where_conditions .= " AND p.diabetes = ?";
}

// Query to fetch patients associated with the logged-in doctor
$query = "
    SELECT DISTINCT p.patient_id, p.name, p.email, p.phone, p.address, p.blood_group, p.high_bp, p.diabetes
    FROM appointment a
    JOIN patient p ON a.patient_id = p.patient_id
    WHERE $where_conditions
";
$stmt = $conn->prepare($query);

// Bind parameters for the doctor ID and any filters
$params = [$doctor_id];
if ($blood_group_filter) {
    $params[] = $blood_group_filter;
}
if ($high_bp_filter !== '') {
    $params[] = $high_bp_filter;
}
if ($diabetes_filter !== '') {
    $params[] = $diabetes_filter;
}

$stmt->bind_param(str_repeat('s', count($params)), ...$params); // Bind all parameters
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Patients</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="dash_styling_doctor.css"> <!-- Your CSS file -->
</head>

<style>
    body {
        font-family: 'Roboto', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f0f4f8;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    h1 {
        margin-top: 40px;
        font-size: 2.5rem;
        color: #333;
        text-align: center;
        font-weight: 600;
        letter-spacing: 1px;
    }

    a {
        color: white;
    }

    /* Filter Form Styling */
    form {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        padding: 20px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin: 30px;
        width: 90%;
        max-width: 1000px;
    }

    label {
        font-size: 1.2rem;
        color: #333;
        font-weight: 500;
    }

    select,
    button {
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 1rem;
        border: 1px solid #ddd;
        background-color: #f6f6f6;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
    }

    select:focus,
    button:focus {
        border-color: #4CAF50;
        outline: none;
    }

    select:hover,
    button:hover {
        background-color: #e3e3e3;
        transform: translateY(-2px);
    }

    button {
        background-image: linear-gradient(to right, #007bff, #00c6ff);
        color: white;
        border: none;
        font-weight: 600;
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
    }

    button:hover {
        background-image: linear-gradient(to right, #0056b3, #00a4cc);
        transform: translateY(-2px);
    }

    /* Patient Cards Container */
    .patient-card-container {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        justify-content: center;
        margin-top: 20px;
        padding: 20px;
        width: 100%;
    }

    /* Individual Patient Card */
    .patient-card {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        width: 280px;
        padding: 20px;
        text-align: left;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .patient-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
    }

    .patient-card h3 {
        font-size: 1.6rem;
        color: #333;
        margin-bottom: 15px;
        text-transform: capitalize;
        font-weight: 600;
    }

    .patient-card p {
        font-size: 1rem;
        color: #555;
        margin: 8px 0;
    }

    .patient-card .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 25px;
        color: white;
        font-size: 0.95rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .patient-card .badge.yes {
        background-color: #28a745;
    }

    .patient-card .badge.no {
        background-color: #dc3545;
    }

    /* Media Queries for Responsiveness */
    @media (max-width: 768px) {
        .patient-card-container {
            flex-direction: column;
            align-items: center;
        }

        .patient-card {
            width: 90%;
        }

        form {
            width: 100%;
            padding: 20px;
        }
    }
</style>

<body>
    <?php include("dash_design_doctor.php"); ?>

    <h1>My Patients</h1>

    <!-- Filter Form -->
    <form method="GET">
        <label for="blood_group">Blood Group:</label>
        <select name="blood_group">
            <option value="">--Select Blood Group--</option>
            <option value="A+" <?php echo ($blood_group_filter == 'A+') ? 'selected' : ''; ?>>A+</option>
            <option value="O+" <?php echo ($blood_group_filter == 'O+') ? 'selected' : ''; ?>>O+</option>
            <option value="B+" <?php echo ($blood_group_filter == 'B+') ? 'selected' : ''; ?>>B+</option>
            <option value="AB+" <?php echo ($blood_group_filter == 'AB+') ? 'selected' : ''; ?>>AB+</option>
            <option value="A-" <?php echo ($blood_group_filter == 'A-') ? 'selected' : ''; ?>>A-</option>
            <option value="O-" <?php echo ($blood_group_filter == 'O-') ? 'selected' : ''; ?>>O-</option>
            <option value="B-" <?php echo ($blood_group_filter == 'B-') ? 'selected' : ''; ?>>B-</option>
            <option value="AB-" <?php echo ($blood_group_filter == 'AB-') ? 'selected' : ''; ?>>AB-</option>
        </select>

        <label for="high_bp">High BP:</label>
        <select name="high_bp">
            <option value="">--Select High BP--</option>
            <option value="1" <?php echo ($high_bp_filter == '1') ? 'selected' : ''; ?>>Yes</option>
            <option value="0" <?php echo ($high_bp_filter == '0') ? 'selected' : ''; ?>>No</option>
        </select>

        <label for="diabetes">Diabetes:</label>
        <select name="diabetes">
            <option value="">--Select Diabetes--</option>
            <option value="1" <?php echo ($diabetes_filter == '1') ? 'selected' : ''; ?>>Yes</option>
            <option value="0" <?php echo ($diabetes_filter == '0') ? 'selected' : ''; ?>>No</option>
        </select>

        <button type="submit"><i class="fas fa-filter"></i> Apply Filters</button>
    </form>

    <!-- Patient Cards -->
    <div class="patient-card-container">
        <?php if ($result->num_rows == 0): ?>
            <p>No patients found based on the selected filters.</p>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="patient-card">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p>Email: <?php echo htmlspecialchars($row['email']); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($row['phone']); ?></p>
                    <p>Blood Group: <?php echo htmlspecialchars($row['blood_group']); ?></p>
                    <p>Address: <?php echo htmlspecialchars($row['address']); ?></p>
                    <p>
                        High BP: <span class="badge <?php echo $row['high_bp'] ? 'yes' : 'no'; ?>">
                            <?php echo $row['high_bp'] ? 'Yes' : 'No'; ?>
                        </span>
                    </p>
                    <p>
                        Diabetes: <span class="badge <?php echo $row['diabetes'] ? 'yes' : 'no'; ?>">
                            <?php echo $row['diabetes'] ? 'Yes' : 'No'; ?>
                        </span>
                    </p>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>

</html>
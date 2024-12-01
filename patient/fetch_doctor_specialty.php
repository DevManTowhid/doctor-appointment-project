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

// Set a specific specialization ID (this could be set dynamically via user input or other means)
$spec_id = 3;

// Prepare the query to select doctor names based on the specialization ID
$query = "SELECT doctor_id, name FROM doctor WHERE spec_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $spec_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!-- HTML for Dropdown -->
<select name="doctor" id="doctor" class="form-control">
    <option value="">Select Doctor</option>
    <?php
    // Populate the dropdown with doctor names
    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . htmlspecialchars($row['doctor_id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
    }
    ?>
</select>

<?php
// Close the statement and connection
$stmt->close();
$conn->close();
?>
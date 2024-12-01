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
$today = date('Y-m-d'); // e.g., 2024-11-19
$patient_id = $_SESSION['patient_id'];

// Fetch doctors with their specialization categories
$query_doctors = "
    SELECT d.doctor_id, d.name, s.category AS specialization
    FROM doctor d
    JOIN specialization s ON d.spec_id = s.spec_id
";
$result_doctors = $conn->query($query_doctors);

// Handle filter submission
$doctor_filter = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : '';
// $date_filter = isset($_POST['date']) ? $_POST['date'] : '';

$query = "
    SELECT a.*, t.timeslot AS timeslot_time
    FROM appointment a
    JOIN timeslots t ON a.timeslot_id = t.timeslot_id
    WHERE a.patient_id = ? AND a.status = 'booked' AND a.date = $today";
$params = [$patient_id];
$conditions = [];

if ($doctor_filter) {
    $conditions[] = "a.doctor_id = ?";
    $params[] = $doctor_filter;
}

// if ($date_filter) {
//     $conditions[] = "DATE(a.date) = ?";
//     $params[] = $date_filter;
// }

if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}



$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat('i', count($params)), ...$params);
$stmt->execute();
$booked_appointments = $stmt->get_result();

// include '../include/dashboard_header.php';
?>
<link rel="stylesheet" href="dash_styling.css">
<div>
    <?php include("dash_design.php"); ?>
</div>
<div class="container mx-auto my-10 p-6 bg-white rounded-lg shadow-lg animate__animated animate__fadeIn">
    <h1 class="text-2xl font-bold text-center mb-6">Upcoming Appointments</h1>

    <!-- Filter Form -->
    <!-- Filter Form -->
    <form method="POST" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <select name="doctor_id" class="form-select block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-300">
                    <option value="">Select Doctor</option>
                    <?php while ($doctor = $result_doctors->fetch_assoc()): ?>
                        <option value="<?= $doctor['doctor_id']; ?>" <?= $doctor_filter == $doctor['doctor_id'] ? 'selected' : ''; ?>>
                            <?= $doctor['name'] . " - " . $doctor['specialization']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="flex items-end"> <!-- Use flex to align button at the bottom -->
                <button type="submit" class="mt-2 w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-500 transition duration-300">Filter</button>
            </div>
        </div>
    </form>

    <!-- Booked Appointments Table -->
    <div class="overflow-x-auto">
        <?php if ($booked_appointments->num_rows > 0): ?>
            <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-blue-600 text-white">
                        <th class="py-2 px-4 text-center">Doctor Name</th>
                        <th class="py-2 px-4 text-center">Specialization</th>
                        <th class="py-2 px-4 text-center">Appointment Date</th>
                        <th class="py-2 px-4 text-center">Time Slot</th>
                        <th class="py-2 px-4 text-center">Status</th>
                        <th class="py-2 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($appointment = $booked_appointments->fetch_assoc()): ?>
                        <?php
                        // Fetch the doctor’s details for each appointment
                        $query_doctor = "SELECT name, s.category AS specialization FROM doctor d JOIN specialization s ON d.spec_id = s.spec_id WHERE doctor_id = ?";
                        $stmt_doctor = $conn->prepare($query_doctor);
                        $stmt_doctor->bind_param('i', $appointment['doctor_id']);
                        $stmt_doctor->execute();
                        $doctor = $stmt_doctor->get_result()->fetch_assoc();
                        ?>
                        <tr class="hover:bg-gray-100 transition duration-300">
                            <td class="py-2 px-4 border-b text-center"><?= $doctor['name']; ?></td>
                            <td class="py-2 px-4 border-b text-center"><?= $doctor['specialization']; ?></td>
                            <td class="py-2 px-4 border-b text-center"><?= date('F j, Y', strtotime($appointment['date'])); ?></td>
                            <td class="py-2 px-4 border-b text-center"><?= $appointment['timeslot_time']; ?></td>
                            <td class="py-2 px-4 border-b text-center"><?= ucfirst($appointment['status']); ?></td>
                            <td class="py-2 px-4 border-b text-center">
                                <form method="POST" action="cancel_appointment.php" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id']; ?>">
                                    <button type="submit" class="bg-red-600 text-white py-1 px-2 rounded-md hover:bg-red-500 transition duration-300">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning mt-4" role="alert">No upcoming appointments found.</div>
        <?php endif; ?>
    </div>
</div>

<?php include '../include/footer.php'; ?>
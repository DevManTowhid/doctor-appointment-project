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

    // Handle filter values
    $doctor_id = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : '';
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    // Base query to fetch appointments
    $query = "
    SELECT 
        a.appointment_id, 
        a.date, 
        ts.time_start, ts.time_end,
        a.status,
        d.name AS doctor_name, 
        s.category AS specialty
    FROM appointment a
    JOIN doctor d ON a.doctor_id = d.doctor_id
    JOIN specialization s ON a.spec_id = s.spec_id
    JOIN timeslots ts ON a.timeslot_id = ts.timeslot_id
    WHERE a.patient_id = ?";

    // Apply filters if values are set
    if ($doctor_id) {
        $query .= " AND a.doctor_id = ?";
    }
    if ($date) {
        $query .= " AND a.date = ?";
    }
    if ($status) {
        $query .= " AND a.status = ?";
    }

    $query .= " ORDER BY a.date DESC, ts.time_start ASC";

    // Prepare the statement
    $stmt = $conn->prepare($query);
    if ($doctor_id && $date && $status) {
        $stmt->bind_param('ssss', $patient_id, $doctor_id, $date, $status);
    } elseif ($doctor_id && $date) {
        $stmt->bind_param('sss', $patient_id, $doctor_id, $date);
    } elseif ($doctor_id && $status) {
        $stmt->bind_param('sss', $patient_id, $doctor_id, $status);
    } elseif ($date && $status) {
        $stmt->bind_param('sss', $patient_id, $date, $status);
    } elseif ($doctor_id) {
        $stmt->bind_param('ss', $patient_id, $doctor_id);
    } elseif ($date) {
        $stmt->bind_param('ss', $patient_id, $date);
    } elseif ($status) {
        $stmt->bind_param('ss', $patient_id, $status);
    } else {
        $stmt->bind_param('s', $patient_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch doctors for the filter
    $doctor_query = "SELECT doctor_id, name FROM doctor";
    $doctor_result = $conn->query($doctor_query);

    // Handle appointment cancellation
    if (isset($_GET['cancel_appointment_id'])) {
        $appointment_id = $_GET['cancel_appointment_id'];

        $cancel_query = "UPDATE appointment SET status = 'cancelled' WHERE appointment_id = ? AND patient_id = ?";
        $cancel_stmt = $conn->prepare($cancel_query);
        $cancel_stmt->bind_param('ss', $appointment_id, $patient_id);
        if ($cancel_stmt->execute()) {
            echo "<script>alert('Appointment cancelled successfully!'); window.location.href='view_appointments.php';</script>";
        } else {
            echo "<script>alert('Failed to cancel appointment. Please try again.');</script>";
        }
    }
    ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>View Appointments</title>
      <link rel="stylesheet" href="dash_styling.css">
      <style>
          t

          /* General Body Styling */
          body {
              font-family: 'Roboto', sans-serif;
              background-color: #f0f4f8;
              color: #333;
              margin: 0;
              padding: 0;
              display: flex;
              flex-direction: column;
              align-items: center;
              justify-content: center;
              min-height: 100vh;
          }

          /* Page Header */
          h1 {
              color: #444;
              font-size: 2rem;
              margin-bottom: 1.5rem;
          }

          /* Filter Form */
          .filter-form {
              display: flex;
              flex-wrap: wrap;
              justify-content: space-between;
              align-items: center;
              background-color: #fff;
              border: 1px solid #ddd;
              border-radius: 8px;
              padding: 1rem;
              margin-bottom: 1.5rem;
              width: 90%;
              max-width: 800px;
              box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
          }

          .filter-form select,
          .filter-form input[type="date"],
          .filter-form button {
              margin: 0.5rem 0;
              padding: 0.5rem;
              border: 1px solid #ddd;
              border-radius: 5px;
              font-size: 1rem;
              outline: none;
          }

          .filter-form select,
          .filter-form input[type="date"] {
              flex: 1 1 calc(30% - 10px);
          }

          .filter-form button {
              background-color: #007bff;
              color: white;
              border: none;
              cursor: pointer;
              flex: 1 1 calc(20% - 10px);
              transition: background-color 0.3s ease;
          }

          .filter-form button:hover {
              background-color: #0056b3;
          }

          /* Responsive Table */
          table {
              width: 90%;
              max-width: 800px;
              border-collapse: collapse;
              margin: 0 auto;
              box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
              background-color: #fff;
              border-radius: 8px;
              overflow: hidden;
          }

          table th,
          table td {
              text-align: left;
              padding: 1rem;
              border-bottom: 1px solid #ddd;
          }

          table th {
              background-color: #007bff;
              color: white;
              font-weight: bold;
          }

          table tr:nth-child(even) {
              background-color: #f9f9f9;
          }

          table tr:hover {
              background-color: #f1f1f1;
          }

          table td a {
              text-decoration: none;
              color: #007bff;
              padding: 0.3rem 0.6rem;
              border: 1px solid #007bff;
              border-radius: 4px;
              transition: background-color 0.3s ease, color 0.3s ease;
          }

          table td a.btn-cancel {
              background-color: #dc3545;
              color: white;
              border: none;
          }

          table td a:hover {
              background-color: #0056b3;
              color: white;
          }

          table td a.btn-cancel:hover {
              background-color: #c82333;
          }

          /* For small screens */
          @media (max-width: 768px) {
              .filter-form {
                  flex-direction: column;
              }

              .filter-form select,
              .filter-form input[type="date"],
              .filter-form button {
                  width: 100%;
                  margin: 0.5rem 0;
              }

              table th,
              table td {
                  padding: 0.7rem;
              }
          }
      </style>
  </head>

  <body>
      <?php include("dash_design.php") ?>

      <h1>Your Appointments</h1>

      <!-- Filter Form -->
      <form class="filter-form" method="POST" action="">
          <select name="doctor_id">
              <option value="">Select Doctor</option>
              <?php while ($doctor = $doctor_result->fetch_assoc()): ?>
                  <option value="<?php echo $doctor['doctor_id']; ?>" <?php echo ($doctor['doctor_id'] == $doctor_id) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($doctor['name']); ?>
                  </option>
              <?php endwhile; ?>
          </select>

          <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>">

          <select name="status">
              <option value="">Select Status</option>
              <option value="Scheduled" <?php echo ($status == 'booked') ? 'selected' : ''; ?>>Scheduled</option>
              <option value="Completed" <?php echo ($status == 'done') ? 'selected' : ''; ?>>Completed</option>
              <option value="Cancelled" <?php echo ($status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
          </select>

          <button type="submit">Apply Filters</button>
      </form>

      <!-- Appointments Table -->
      <table class="responsive-table">
          <thead>
              <tr>
                  <th>Appointment ID</th>
                  <th>Doctor</th>
                  <th>Specialty</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Status</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody>
              <?php while ($appointment = $result->fetch_assoc()): ?>
                  <tr>
                      <td><?php echo htmlspecialchars($appointment['appointment_id']); ?></td>
                      <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                      <td><?php echo htmlspecialchars($appointment['specialty']); ?></td>
                      <td><?php echo htmlspecialchars($appointment['date']); ?></td>
                      <td>
                          <?php echo htmlspecialchars($appointment['time_start']) . " - " . htmlspecialchars($appointment['time_end']); ?>
                      </td>
                      <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                      <td>
                          <?php if ($appointment['status'] === 'booked'): ?>
                              <a href="edit_appointment.php?appointment_id=<?php echo $appointment['appointment_id']; ?>" class="btn-edit">Edit</a>
                              <a href="view_appointments.php?cancel_appointment_id=<?php echo $appointment['appointment_id']; ?>" class="btn-cancel" onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</a>
                          <?php endif; ?>

                      </td>
                  </tr>
              <?php endwhile; ?>
          </tbody>
      </table>
  </body>

  </html>
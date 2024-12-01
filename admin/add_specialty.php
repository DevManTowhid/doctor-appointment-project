<?php
session_start();
include('../include/db.php');

// Check if the admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== "admin") {
    header('Location: ../login.php');
    exit();
}

// Handle form submission
if (isset($_POST['submit'])) {
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    // Check if the category is empty
    if (empty($category)) {
        $error_message = "Category cannot be empty.";
    } else {
        // Check if the category already exists in the database
        $check_query = "SELECT * FROM specialization WHERE category = '$category'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            // Category already exists
            $error_message = "Specialization category already exists.";
        } else {
            // Insert new category into the specialization table
            $query = "INSERT INTO specialization (category) VALUES ('$category')";
            if (mysqli_query($conn, $query)) {
                $success_message = "Specialization added successfully.";
            } else {
                $error_message = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch current list of specializations and doctor count
$specialization_query = "SELECT s.category, COUNT(d.doctor_id) AS doctor_count
                         FROM specialization s
                         LEFT JOIN doctor d ON s.spec_id = d.spec_id
                         GROUP BY s.spec_id";
$specialization_result = mysqli_query($conn, $specialization_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Specialization</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="dash_styling.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-container {
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <?php
    include('dash_design.php');
    ?>
    <div class="container">
        <h2>Add Specialization</h2>
        <div class="form-container">
            <?php
            if (isset($success_message)) {
                echo "<div class='alert alert-success'>$success_message</div>";
            }
            if (isset($error_message)) {
                echo "<div class='alert alert-danger'>$error_message</div>";
            }
            ?>

            <form action="add_specialty.php" method="POST">
                <div class="form-group">
                    <label for="category">Specialization Category</label>
                    <input type="text" class="form-control" id="category" name="category" placeholder="Enter specialization category" required>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Add Specialization</button>
            </form>
        </div>

        <!-- Current list of Specializations -->
        <div class=" table-responsive">
            <h3>Current Specializations</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Specialization Category</th>
                        <th>Number of Doctors</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($specialization_result)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo $row['doctor_count']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

</body>

</html>
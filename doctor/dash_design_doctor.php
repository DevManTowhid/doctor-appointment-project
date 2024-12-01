<?php
// Check if the session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="sidebar">
    <h3 class="text-center text-white mb-4 text-color:white">Doctor Panel</h3>
    <a href="dashboard.php">Dashboard</a>
    <a href="view_doctors.php">View Doctors</a>
    <a href="view_appointments.php">View Appointments</a>
    <a href="edit_profile.php">Edit Profile</a>

    <a href="blood_group_query.php">Blood Group</a>
    <a href="hypertension&diabetes.php">hypertension and diabetes</a>
    <a href="../logout.php">Logout</a>

</div>
<style>
    *:hover {
        cursor: pointer;
    }
</style>
<!-- Main Content -->
<div class="content" style="flex-direction: row; flex:1">
    <header>
        <h1 style="color:white">Doctor Dashboard</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="view_doctors.php">View Doctors</a>
            <a href="view_appointments.php">View Appointments</a>
            <a href="edit_profile.php">Edit Profile</a>

            <a href="blood_group_query.php">Blood Group</a>
            <a href="hypertension&diabetes.php">hypertension and diabetes</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>
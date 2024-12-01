<?php
// Check if the session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="dash_styling.css">
<div class="sidebar">
    <h3 class="text-center text-white mb-4 text-color:white">Admin Panel</h3>
    <a href="dashboard.php">Dashboard</a>
    <a href="view_doctors.php">View Doctors</a>
    <a href="view_appointments.php">View Appointments</a>
    <a href="edit_profile.php">Edit Profile</a>
    <a href="add_doctor.php">Add Doctor</a>
    <a href="add_admin.php">Add Admin</a>
    <a href="add_specialty.php">Add Specialty</a>
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
        <h1 style="color:white">Admin Dashboard</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="view_doctors.php">View Doctors</a>
            <a href="view_appointments.php">View Appointments</a>
            <a href="edit_profile.php">Edit Profile</a>
            <a href="add_doctor.php">Add Doctor</a>
            <a href="add_admin.php">Add Admin</a>
            <a href="add_specialty.php">Add Specialty</a>
            <a href="blood_group_query.php">Blood Group</a>
            <a href="hypertension&diabetes.php">hypertension and diabetes</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>
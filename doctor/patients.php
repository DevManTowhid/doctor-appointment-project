<?php

// Query to fetch patients associated with the logged-in doctor
$query = "
SELECT DISTINCT p.patient_id
JOIN patient p ON a.patient_id = p.patient_id

";

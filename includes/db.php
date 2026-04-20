<?php
if (PHP_OS_FAMILY === 'Darwin') {
    $dbc = mysqli_connect("localhost", "root", "root", "fueltrackpro_db");
} else {
    $dbc = mysqli_connect("localhost", "root", "", "fueltrackpro_db");
}

if (!$dbc) {
    die("Connection failed: " . mysqli_connect_error());
}

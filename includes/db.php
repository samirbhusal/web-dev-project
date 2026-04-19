<?php
$dbc = mysqli_connect("localhost", "root", "root", "fueltrackpro_db");

if (!$dbc) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<?php
$host = "localhost";
$user = "root"; // default sa XAMPP
$pass = "";     // usually empty
$db   = "bolim portfolio";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>

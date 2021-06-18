<?php
require_once('config/config.php');

$data_json = json_decode(file_get_contents('php://input'), true);

if ($data_json == null) exit(1);

$data = $data_json['data'];
$epoch = time();

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "INSERT INTO matriculas (epoch, matricula) VALUES ('$epoch', '$data')";

// Perform a query, check for error
if (!mysqli_query($conn, $sql)) {
    die("Error description: " . mysqli_error($conn));
}

echo "Insertado";

mysqli_close($conn);

?>
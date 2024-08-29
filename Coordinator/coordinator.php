<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $coordinator_name = $_POST['coordinator_name'];
    $coordinator_email = $_POST['coordinator_email'];
    // $course_name = $_POST['course_name'];

    $stmt = $conn->prepare("INSERT INTO coordinator (name, email, course_name) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $coordinator_name, $coordinator_email, $course_name);

    if ($stmt->execute()) {
        echo "New coordinator added successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    header("Location: coordinator-interface.php");
    exit();

    $stmt->close();
}

$conn->close();

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $coordinator_name = $_POST['coordinator_name'];
    $coordinator_email = $_POST['coordinator_email'];
    // $course_name = $_POST['course_name'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO coordinator (name, email, course_name) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $coordinator_name, $coordinator_email, $course_name);

    // Execute and check if the record was inserted successfully
    if ($stmt->execute()) {
        echo "New coordinator added successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
}

$conn->close();
?>